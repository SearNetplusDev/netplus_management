<?php

namespace App\Strategies\v1\Accounting\DTE;

use App\Enums\v1\Billing\DocumentTypes;
use App\Models\Billing\InvoiceModel;
use App\Models\Billing\PaymentModel;
use App\Models\Clients\ClientModel;
use Illuminate\Support\Collection;

class CreditoFiscalStrategy extends BaseDTEStrategy
{
    /***
     * Crédito fiscal exige un orden específico de campos en
     * "identificacion" diferente al orden base.
     *
     * @return true[]
     */
    protected function identificacionSchema(): array
    {
        return [
            'version' => true,
            'ambiente' => true,
            'tipoDte' => true,
            'numeroControl' => true,
            'codigoGeneracion' => true,
            'tipoModelo' => true,
            'tipoOperacion' => true,
            'tipoContingencia' => true,
            'motivoContin' => true,
            'fecEmi' => true,
            'horEmi' => true,
            'tipoMoneda' => true,
        ];
    }

    /***
     * Detecta el origen del DTE y delega al escenario correspondiente.
     *
     * @param array $data
     * @return array
     * @throws \Random\RandomException
     */
    protected function buildBody(array $data): array
    {
        return match (true) {
            isset($data['payment']) => $this->buildFromPayment((int)$data['payment']),
            $data['source'] === 'manual' => $this->buildFromManual($data),
            $data['source'] === 'invoices' => $this->buildFromSelectedInvoices($data),
            default => throw new \InvalidArgumentException("Origen no soportado: {$data['source']}"),
        };
    }

    /***
     * Escenario 1 - Desde un pago previamente registrado.
     * Construye el DTE a partir de un pago ya registrado en el sistema.
     *
     * @param int $paymentId
     * @return array
     * @throws \Random\RandomException
     */
    private function buildFromPayment(int $paymentId): array
    {
        $payment = PaymentModel::query()
            ->with([
                'client.corporate_info.activity',
                'client.corporate_info.state',
                'client.corporate_info.municipality',
                'client.nit',
                'client.mobile',
                'client.email',
                'invoices.items',
                'invoices.period',
                'payment_method',
            ])
            ->where('id', $paymentId)
            ->where('status_id', true)
            ->firstOrFail();

        $client = $payment->client;
        $retainedIva = (bool)($client->corporate_info?->retained_iva ?? false);
        $discount = (float)($payment->discount_amount ?? 0);
        [$body, $gravado] = $this->buildFromInvoices($payment->invoices);

        return $this->assembleDocument(
            client: $client,
            body: $body,
            gravado: (float)$gravado,
            retainedIva: $retainedIva,
            discount: (float)$discount,
            condition: 1,
            method: $payment->payment_method?->code,
        );
    }

    /***
     * Escenario 2 - Formulario desde el frontend.
     * Genera el DTE a partir de los datos ingresados manualmente por el usuario.
     *
     * @param array $data
     * @return array
     * @throws \Random\RandomException
     */
    private function buildFromManual(array $data): array
    {
        $client = $this->loadClient((int)$data['client_id']);
        $retainedIva = (($data['totals']['iva_retenido'] ?? 0) > 0) || (bool)($client->corporate_info?->retained_iva ?? false);
        $discount = round((float)($data['totals']['discount'] ?? 0), 2);
        [$body, $gravado] = $this->buildFromItems($data['items']);

        return $this->assembleDocument(
            client: $client,
            body: $body,
            gravado: (float)$gravado,
            retainedIva: $retainedIva,
            discount: (float)$discount,
            condition: (int)($data['payment_condition']),
            method: $this->paymentMethodCode((int)$data['payment_method']),
        );
    }

    /***
     * Escenario 3 - Selección de facturas desde el frontend.
     * Crea el DTE a partir de facturas seleccionadas por el usuario.
     *
     * @param array $data
     * @return array
     * @throws \Random\RandomException
     */
    private function buildFromSelectedInvoices(array $data): array
    {
        $client = $this->loadClient((int)$data['client_id']);
        $invoices = InvoiceModel::query()
            ->with(['items', 'period'])
            ->whereIn('id', $data['items'])
            ->where('client_id', $data['client_id'])
            ->get();

        $retainedIva = (($data['totals']['iva_retenido'] ?? 0) > 0) || (bool)($client->corporate_info?->retained_iva ?? false);
        $discount = round((float)($data['totals']['discount'] ?? 0), 2);
        [$body, $gravado] = $this->buildFromInvoices($invoices);

        return $this->assembleDocument(
            client: $client,
            body: $body,
            gravado: (float)$gravado,
            retainedIva: $retainedIva,
            discount: (float)$discount,
            condition: (int)($data['payment_condition']),
            method: $this->paymentMethodCode((int)$data['payment_method']),
        );
    }

    /***
     * Carga el cliente con todas las relaciones necesarias para crear el DTE.
     *
     * @param int $clientId
     * @return ClientModel
     */
    private function loadClient(int $clientId): ClientModel
    {
        return ClientModel::query()
            ->with([
                'corporate_info.activity',
                'corporate_info.state',
                'corporate_info.municipality',
                'nit',
                'mobile',
                'email',
            ])
            ->findOrFail($clientId);
    }

    /***
     * Ensambla la estructura completa del DTE.
     *
     * @param ClientModel $client
     * @param array $body
     * @param float $gravado
     * @param bool $retainedIva
     * @param float $discount
     * @param int $condition
     * @param string|null $method
     * @return array
     * @throws \Random\RandomException
     */
    private function assembleDocument(
        ClientModel $client,
        array       $body,
        float       $gravado,
        bool        $retainedIva,
        float       $discount,
        int         $condition,
        ?string     $method,
    ): array
    {
        return [
            'identificacion' => $this->identificacion(DocumentTypes::CREDITO_FISCAL, 3),
            'documentoRelacionado' => null,
            'emisor' => $this->emisorBase(),
            'receptor' => $this->buildReceptor($client),
            'otrosDocumentos' => null,
            'ventaTercero' => null,
            'cuerpoDocumento' => $body,
            'resumen' => $this->buildResumen(
                gravado: $gravado,
                retainedIva: $retainedIva,
                discount: $discount,
                condition: $condition,
                method: $method ?? '01',
            ),
            'extension' => null,
            'apendice' => null,
        ];
    }

    /***
     * Itera las facturas con sus ítems y construye las líneas del cuerpoDocumento.
     *
     * @param Collection $invoices
     * @return array
     */
    private function buildFromInvoices(Collection $invoices): array
    {
        $numItem = 1;
        $gravado = 0;
        $body = [];

        foreach ($invoices as $invoice) {
            $period = $invoice->period?->name;

            foreach ($invoice->items as $item) {
                $precioUni = (float)$item->unit_price;
                $gravada = $precioUni * (int)$item->quantity;
                $body[] = $this->buildLineItem(
                    num: $numItem++,
                    tipoItem: 2,
                    descripcion: "{$item->description} ({$period})",
                    cantidad: (int)$item->quantity,
                    precioUni: $precioUni,
                    gravada: $gravada,
                );

                $gravado += $gravada;
            }
        }

        return [$body, $gravado];
    }

    /***
     * Construye las líneas del cuerpoDocumento desde ítems ingresados manualmente.
     *
     * @param array $items
     * @return array
     */
    private function buildFromItems(array $items): array
    {
        $gravado = 0;
        $body = [];

        foreach ($items as $item) {
            $precioUni = (float)$item['unit_price'] / parent::TASA_VALOR_NETO;
            $gravada = $precioUni * (int)$item['quantity'];

            $body[] = $this->buildLineItem(
                num: (int)$item['_line'],
                tipoItem: (int)$item['item_type'],
                descripcion: $item['description'],
                cantidad: (int)$item['quantity'],
                precioUni: $precioUni,
                gravada: $gravada,
            );

            $gravado += $gravada;
        }

        return [$body, $gravado];
    }

    /***
     * Construye las líneas del cuerpoDocumento con los campos fijos requeridos del crédito fiscal.
     *
     * @param int $num
     * @param int $tipoItem
     * @param string $descripcion
     * @param int $cantidad
     * @param float $precioUni
     * @param float $gravada
     * @return array
     */
    private function buildLineItem(
        int    $num,
        int    $tipoItem,
        string $descripcion,
        int    $cantidad,
        float  $precioUni,
        float  $gravada,
    ): array
    {
        return [
            'numItem' => $num,
            'tipoItem' => $tipoItem,
            'numeroDocumento' => null,
            'codigo' => null,
            'codTributo' => null,
            'descripcion' => $descripcion,
            'cantidad' => $cantidad,
            'uniMedida' => 99,
            'precioUni' => $precioUni,
            'montoDescu' => 0,
            'ventaNoSuj' => 0,
            'ventaExenta' => 0,
            'ventaGravada' => $gravada,
            'tributos' => ['20'],
            'psv' => 0,
            'noGravado' => 0,
        ];
    }

    /***
     * Construye el bloque "receptor" a partir de ClientModel.
     * @param ClientModel $client
     * @return array
     */
    private function buildReceptor(ClientModel $client): array
    {
        $fi = $client->corporate_info;

        return [
            'nit' => $fi ? $this->parseNumber($fi->nit) : null,
            'nrc' => $fi ? $this->parseNumber($fi->nrc) : null,
            'nombre' => $fi?->invoice_alias ?? "{$client->name} {$client->surname}",
            'codActividad' => $fi?->activity?->code,
            'descActividad' => $fi?->activity?->name,
            'nombreComercial' => $fi?->invoice_alias ?? "{$client->name} {$client->surname}",
            'direccion' => [
                'departamento' => $fi?->state?->code ?? $client->address?->state?->code,
                'municipio' => $fi?->municipality?->code ?? $client->address?->municipality?->code,
                'complemento' => $fi?->address ?? $client->address?->address,
            ],
            'telefono' => $this->phoneFormatter($fi?->phone_number ?? $client->mobile?->number) ?? null,
            'correo' => $client->email?->email,
        ];
    }

    /***
     * Construye el bloque "resumen" con la lógica financiera del crédito fiscal.
     *
     * @param float $gravado
     * @param bool $retainedIva
     * @param float $discount
     * @param int $condition
     * @param string|null $method
     * @return array
     */
    private function buildResumen(
        float   $gravado,
        bool    $retainedIva,
        float   $discount,
        int     $condition,
        ?string $method,
    ): array
    {
        //  Total bruto con Iva antes del descuento
        $totalConIva = $gravado * parent::TASA_VALOR_NETO;

        //  Aplicar descuento al total con Iva y extraer el neto resultante
        $totalDescontado = $totalConIva - $discount;
        $neto = $totalDescontado / parent::TASA_VALOR_NETO;
        $iva = $neto * parent::TASA_IVA;

        //  Iva Retenido; 1% del valor neto si aplica y el total supera los $100
        $ivaRetenido = ($retainedIva && $totalDescontado > 100) ? $neto * parent::TASA_IVA_RETENIDO : 0;
        $totalPagar = $neto + $iva - $ivaRetenido;

        return [
            'totalNoSuj' => 0,
            'totalExenta' => 0,
            'totalGravado' => round($neto, 2),
            'subTotalVentas' => round($neto, 2),
            'descuNoSuj' => 0,
            'descuExenta' => 0,
            'descuGravado' => $discount,
            'porcentajeDescuento' => 0,
            'totalDescu' => $discount,
            'tributos' => [
                [
                    'codigo' => '20',
                    'descripcion' => 'Impuesto al Valor Agregado 13%',
                    'valor' => round($iva, 2),
                ],
            ],
            'subTotal' => round($neto, 2),
            'ivaPerci1' => 0,
            'ivaRete1' => round($ivaRetenido, 2),
            'totalNoGravado' => 0,
            'totalPagar' => round($totalPagar, 2),
            'totalLetras' => $this->numberToLetter->convert(round($totalPagar, 2)),
            'saldoFavor' => 0,
            'condicionOperacion' => $condition,
            'pagos' => [
                [
                    'codigo' => $method,
                    'montoPago' => round($totalPagar, 2),
                    'referencia' => null,
                    'plazo' => null,
                    'periodo' => null,
                ],
            ],
            'numPagoElectronico' => null,
        ];
    }

}
