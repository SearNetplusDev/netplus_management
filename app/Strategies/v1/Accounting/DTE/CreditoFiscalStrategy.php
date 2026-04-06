<?php

namespace App\Strategies\v1\Accounting\DTE;

use App\Enums\v1\Billing\DocumentTypes;
use App\Models\Billing\InvoiceModel;
use App\Models\Billing\PaymentModel;
use App\Models\Clients\ClientModel;

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
     * Verifica el metodo con el que se origina el DTE y redirige a su respectiva función.
     * - Id de pago
     * - Llenado de formulario
     * - Por selección de facturas
     *
     * @param array $data
     * @return array
     */
    protected function buildBody(array $data): array
    {
        if (isset($data['payment'])) {
            return $this->buildFromPayment((int)$data['payment']);
        }

        return match ($data['source']) {
            'manual' => $this->buildFromManual($data),
            'invoices' => $this->buildFromInvoices($data),
            default => throw new \InvalidArgumentException("Fuente no soportada: {$data['source']}"),
        };

    }

    /***
     * Escenario 1 - Id de Pago.
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
                'payment_method'
            ])
            ->findOrFail($paymentId);

        $client = $payment->client;
        $receptor = $this->buildReceptorFromClient($client);
        $items = $this->resolveItemsFromInvoices($payment->invoices->flatMap->items);
        $totals = $this->calculateTotals(
            rawTotal: (float)$payment->amount,
            discount: (float)$payment->discount_amount ?? 0,
            ivaRetenido: $client->corporate_info?->retained_iva ?? false,
        );

        return $this->buildDocument(
            receptor: $receptor,
            cuerpoDocumento: $items,
            totals: $totals,
            condicionOperacion: 1,
            paymentMethodCode: $payment->payment_method->code,
        );

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

        $receptor = $this->buildReceptorFromClient($client);
        $useIvaRetenido = (($data['totals']['iva_retenido'] ?? 0) > 0) || ($client->corporate_info?->retained_iva ?? false);
        $totals = $this->calculateTotals(
            rawTotal: (float)$data['totals']['total'],
            discount: (float)$data['totals']['discount'] ?? 0,
            ivaRetenido: $useIvaRetenido,
        );
        $items = $this->mapManualItems($data['items']);

        return $this->buildDocument(
            receptor: $receptor,
            cuerpoDocumento: $items,
            totals: $totals,
            condicionOperacion: $data['payment_condition'],
            paymentMethodCode: $this->paymentMethodCode($data['payment_method']),
        );
    }

    /***
     *  Escenario 3 - Selección de facturas desde el frontend
     *  Crea el crédito fiscal a partir de las facturas seleccionadas por el usuario en el frontend.
     *
     * @param array $data
     * @return array
     * @throws \Random\RandomException
     */
    private function buildFromInvoices(array $data): array
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

        $receptor = $this->buildReceptorFromClient($client);
        $useIvaRetenido = (($data['totals']['iva_retenido'] ?? 0) > 0) || ($client->corporate_info?->retained_iva ?? false);
        $totals = $this->calculateTotals(
            rawTotal: (float)$data['totals']['total'],
            discount: (float)$data['totals']['discount'] ?? 0,
            ivaRetenido: $useIvaRetenido,
        );
        $items = $this->resolveItemsFromInvoices($invoices->flatMap->items);

        return $this->buildDocument(
            receptor: $receptor,
            cuerpoDocumento: $items,
            totals: $totals,
            condicionOperacion: $data['payment_condition'],
            paymentMethodCode: $this->paymentMethodCode($data['payment_method']),
        );
    }

    /***
     * Construye el array con los datos del receptor.
     *
     * @param ClientModel $client
     * @return array
     */
    private function buildReceptorFromClient(ClientModel $client): array
    {
        $fi = $client->corporate_info;

        return [
            'nit' => $fi ? $this->parseNumber($fi->nit) : null,
            'nrc' => $fi ? $this->parseNumber($fi->nrc) : null,
            'nombre' => $fi?->invoice_alias ?? "{$client->name} {$client->surname}",
            'codActividad' => $fi?->activity?->code,
            'descActividad' => $fi->activity?->name,
            'nombreComercial' => $fi?->invoice_alias ?? "{$client->name} {$client->surname}",
            'direccion' => [
                'departamento' => $fi?->state?->code ?? $client->address?->state?->code,
                'municipio' => $fi?->municipality->code ?? $client->address?->municipality?->code,
                'complemento' => $fi->address ?? $client->address?->address,
            ],
            'telefono' => $this->phoneFormatter($fi?->phone_number ?? $client->mobile->number ?? ''),
            'correo' => $client->email?->email,
        ];
    }

    /***
     * Mapea los items de cada factura al formato del cuerpo del DTE.
     *
     * @param iterable $items
     * @return array
     */
    private function resolveItemsFromInvoices(iterable $items): array
    {
        $result = [];
        $line = 1;

        foreach ($items as $item) {
            $precioUni = $this->round2((float)$item->unit_price);
            $gravado = $this->round2($precioUni * (int)$item->quantity);

            $result[] = [
                'numItem' => $line++,
                'tipoItem' => 2,
                'numeroDocumento' => null,
                'codigo' => null,
                'codTributo' => null,
                'descripcion' => $item->description,
                'cantidad' => (int)$item->quantity,
                'uniMedida' => 99,
                'precioUni' => $precioUni,
                'montoDescu' => 0,
                'ventaNoSuj' => 0,
                'ventaExenta' => 0,
                'ventaGravada' => $gravado,
                'tributos' => ['20'],
                'psv' => 0,
                'noGravado' => 0,
            ];
        }

        return $result;
    }

    /***
     * Mapea los items ingresados manualmente para registrarlos en el DTE.
     *
     * @param array $items
     * @return array
     */
    private function mapManualItems(array $items): array
    {
        $result = [];

        foreach ($items as $item) {
            $result[] = [
                'numItem' => $item['_line'],
                'tipoItem' => $item['item_type'],
                'numeroDocumento' => null,
                'codigo' => null,
                'codTributo' => null,
                'descripcion' => $item['description'],
                'cantidad' => $item['quantity'],
                'uniMedida' => 99,
                'precioUni' => $item['neto'],
                'montoDescu' => 0,
                'ventaNoSuj' => 0,
                'ventaExenta' => 0,
                'ventaGravada' => $item['neto'] * $item['quantity'],
                'tributos' => ['20'],
                'psv' => 0,
                'noGravado' => 0,
            ];
        }

        return $result;
    }

    /***
     * Calculos financieros para el resumen del DTE.
     *
     * @param float $rawTotal
     * @param float $discount
     * @param bool $ivaRetenido
     * @return array
     */
    private function calculateTotals(float $rawTotal, float $discount, bool $ivaRetenido): array
    {
        $base = $rawTotal - $discount;
        $neto = $this->round2($base / 1.13);
        $iva = $this->round2($neto * 0.13);
        $ivaRetAmt = ($ivaRetenido && $base > 100) ? $this->round2($neto * 0.01) : 0;
        $totalPagar = $this->round2($neto + $iva - $ivaRetAmt);

        return [
            'neto' => $neto,
            'iva' => $iva,
            'ivaRetenido' => $ivaRetAmt,
            'discount' => $discount,
            'totalPagar' => $totalPagar,
        ];
    }

    /***
     * Crea el json con los datos del DTE.
     *
     * @param array $receptor
     * @param array $cuerpoDocumento
     * @param array $totals
     * @param int $condicionOperacion
     * @param string $paymentMethodCode
     * @return array
     * @throws \Random\RandomException
     */
    private function buildDocument(
        array  $receptor,
        array  $cuerpoDocumento,
        array  $totals,
        int    $condicionOperacion,
        string $paymentMethodCode
    ): array
    {
        return [
            'identificacion' => $this->identificacion(DocumentTypes::CREDITO_FISCAL, 3),
            'documentoRelacionado' => null,
            'emisor' => $this->emisorBase(),
            'receptor' => $receptor,
            'otrosDocumentos' => null,
            'ventaTercero' => null,
            'cuerpoDocumento' => $cuerpoDocumento,
            'resumen' => [
                'totalNoSuj' => 0,
                'totalExenta' => 0,
                'totalGravado' => $totals['neto'],
                'subTotalVentas' => $totals['neto'],
                'descuNoSuj' => 0,
                'descuExenta' => 0,
                'descuGravada' => $this->round2($totals['discount']),
                'porcentajeDescuento' => 0,
                'totalDescu' => $this->round2($totals['discount']),
                'tributos' => [
                    [
                        'codigo' => '20',
                        'descripcion' => 'Impuesto al Valor Agregado 13%',
                        'valor' => $this->round2($totals['iva']),
                    ],
                ],
                'subTotal' => $totals['neto'],
                'ivaPerci1' => 0,
                'ivaRete1' => $totals['ivaRetenido'],
                'reteRenta' => 0,
                'montoTotalOperacion' => $totals['totalPagar'],
                'totalNoGravado' => 0,
                'totalPagar' => $totals['totalPagar'],
                'totalLetras' => $this->numberToLetter->convert($totals['totalPagar']),
                'saldoFavor' => 0,
                'condicionOperacion' => $condicionOperacion,
                'pagos' => [
                    [
                        'codigo' => $paymentMethodCode,
                        'montoPago' => $totals['totalPagar'],
                        'referencia' => null,
                        'plazo' => null,
                        'periodo' => null,
                    ],
                ],
                'numPagoElectronico' => null,
            ],
            'extension' => null,
            'apendice' => null,
        ];
    }
}
