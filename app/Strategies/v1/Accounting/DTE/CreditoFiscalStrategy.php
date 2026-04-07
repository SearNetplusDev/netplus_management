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
     *  Verifica el metodo con el que se origina el DTE y redirige a su respectiva función.
     *  - Id de pago
     *  - Llenado de formulario
     *  - Por selección de facturas
     *
     * @param array $data
     * @return array
     * @throws \Random\RandomException
     */
    protected function buildBody(array $data): array
    {
        if (isset($data['payment'])) {
            return $this->buildFromPayment((int)$data['payment']);
        }

        return match ($data['source']) {
            'manual' => $this->buildFromManual($data),
            'invoices' => $this->buildFromSelectedInvoices($data),
            default => throw new \InvalidArgumentException("Fuente no soportada: {$data['source']}"),
        };

    }

    /***
     * Escenario 1 - Desde un pago ya registrado.
     * Construye el crédito fiscal a partir del id del pago ingresado en el sistema.
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
                'payment_method'
            ])
            ->where('id', $paymentId)
            ->where('status_id', true)
            ->firstOrFail();

        $client = $payment->client;
        $retainedIva = (bool)$client->corporate_info?->retained_iva ?? false;
        $paymentDiscount = $this->round2((float)$payment->discount_amount ?? 0);
        [$body, $gravado] = $this->buildFromInvoices($payment->invoices);

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
                discount: $paymentDiscount,
                condition: 1,
                method: $payment->payment_method?->code,
            ),
            'extension' => null,
            'apendice' => null,
        ];
    }

    /***
     *  Escenario 2 - Llenado de formulario desde el frontend
     *  Genera el crédito fiscal a partir de los datos ingresados por el usuario en el frontend.
     *
     * @param array $data
     * @return array
     * @throws \Random\RandomException
     */
    private function buildFromManual(array $data): array
    {
        $client = ClientModel::query()
            ->with([
                'corporate_info.activity',
                'corporate_info.state',
                'corporate_info.municipality',
                'nit',
                'mobile',
                'email',
            ])
            ->findOrFail($data['client_id']);

        $retainedIva = (($data['totals']['iva_retenido'] ?? 0) > 0) || (bool)$client->corporate_info?->retained_iva ?? false;
        $manualDiscount = $this->round2((float)$data['totals']['discount'] ?? 0);
        [$body, $gravado] = $this->buildFromItems($data['items']);

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
                discount: $manualDiscount,
                condition: (int)$data['payment_condition'],
                method: $this->paymentMethodCode((int)$data['payment_method']),
            ),
            'extension' => null,
            'apendice' => null,
        ];
    }

    /***
     *  Escenario 3 - Selección de facturas desde el frontend
     *  Crea el crédito fiscal a partir de las facturas seleccionadas por el usuario en el frontend.
     *
     * @param array $data
     * @return array
     * @throws \Random\RandomException
     */
    private function buildFromSelectedInvoices(array $data): array
    {
        $client = ClientModel::query()
            ->with([
                'corporate_info.activity',
                'corporate_info.state',
                'corporate_info.municipality',
                'nit',
                'mobile',
                'email',
            ])
            ->findOrFail($data['client_id']);

        $invoices = InvoiceModel::query()
            ->with(['items', 'period'])
            ->whereIn('id', $data['items'])
            ->where('client_id', $data['client_id'])
            ->get();

        $retainedIva = (($data['totals']['iva_retenido'] ?? 0) > 0) || (bool)($client->corporate_info?->retained_iva ?? false);
        $invoiceDiscount = $this->round2((float)$data['totals']['discount'] ?? 0);
        [$body, $gravado] = $this->buildFromInvoices($invoices);

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
                discount: $invoiceDiscount,
                condition: (int)$data['payment_condition'],
                method: $this->paymentMethodCode((int)$data['payment_method']),
            ),
            'extension' => null,
            'apendice' => null,
        ];
    }

    /***
     * Itera la colección de las facturas junto con sus ítems para construir el cuerpo del documento
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

                $body[] = [
                    'numItem' => $numItem++,
                    'tipoItem' => 2,
                    'numeroDocumento' => null,
                    'codigo' => null,
                    'codTributo' => null,
                    'descripcion' => "{$item->description} ({$period})",
                    'cantidad' => (int)$item->quantity,
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

                $gravado += $gravada;
            }
        }

        return [$body, $gravado];
    }

    /***
     * Construye el cuerpo del documento desde el array de ítems ingresados manualmente.
     *
     * @param array $items
     * @return array
     */
    private function buildFromItems(array $items): array
    {
        $gravado = 0;
        $body = [];

        foreach ($items as $item) {
            $unitPrice = (float)$item['unit_price'] / 1.13;
            $gravada = (float)$unitPrice * (int)$item['quantity'];

            $body[] = [
                'numItem' => $item['_line'],
                'tipoItem' => $item['item_type'],
                'numeroDocumento' => null,
                'codigo' => null,
                'codTributo' => null,
                'descripcion' => $item['description'],
                'cantidad' => (int)$item['quantity'],
                'uniMedida' => 99,
                'precioUni' => $unitPrice,
                'montoDescu' => 0,
                'ventaNoSuj' => 0,
                'ventaExenta' => 0,
                'ventaGravada' => $gravada,
                'tributos' => ['20'],
                'psv' => 0,
                'noGravado' => 0,
            ];

            $gravado += $gravada;
        }

        return [$body, $gravado];
    }

    /***
     * Crea el apartado receptor a partir de ClientModel.
     *
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
                'complemento' => $fi?->address ?? $client?->address?->address,
            ],
            'telefono' => $this->phoneFormatter($fi?->phone_number ?? $client?->mobile?->number ?? ''),
            'correo' => $client->email?->email,
        ];
    }

    /***
     * Construye el apartado resumen con toda la lógica financiera.
     *
     * @param float $gravado
     * @param bool $retainedIva
     * @param float $discount
     * @param int $condition
     * @param string $method
     * @return array
     */
    private function buildResumen(
        float  $gravado,
        bool   $retainedIva,
        float  $discount,
        int    $condition,
        string $method,
    ): array
    {
        $rawIva = $gravado * 0.13;
        $rawBruto = $gravado + $rawIva;
        $rawTotal = $rawBruto - $discount;

        $neto = $rawTotal / 1.13;
        $iva = $neto * 0.13;
        $ivaRetenido = ($retainedIva && $rawTotal > 100) ? $neto * 0.01 : 0;
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
                'codigo' => $method,
                'montoPago' => round($totalPagar, 2),
                'referencia' => null,
                'plazo' => null,
                'periodo' => null,
            ],
            'numPagoElectronico' => null,
        ];
    }
}
