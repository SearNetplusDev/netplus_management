<?php

namespace App\Strategies\v1\Accounting\DTE;

use App\Enums\v1\Billing\DocumentTypes;
use App\Models\Billing\Options\PaymentMethodModel;
use App\Models\Billing\PaymentModel;
use App\Models\Clients\ClientModel;

class FacturaStrategy extends BaseDTEStrategy
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
            'fecEmi' => true,
            'horEmi' => true,
            'tipoMoneda' => true,
            'tipoContingencia' => true,
            'motivoContin' => true,
        ];
    }

    /***
     *  Router principal: detecta el escenario según los datos recibidos.
     * @param array $data
     * @return array
     * @throws \Random\RandomException
     */
    protected function buildBody(array $data): array
    {
        if (isset($data['payment'])) {
            return $this->buildFromPayment((int)$data['payment']);
        }

        if (isset($data['client_id'])) {
            return $this->buildFromManualData($data);
        }

        throw new \InvalidArgumentException("Datos insuficientes");
    }

    /***
     * Escenario 1 - Desde un pago ya registrado
     *
     * Construye el DTE a partir de un pago ya existente.
     * Carga las facturas, sus ítems, el período y los datos del cliente.
     *
     * @param int $paymentId
     * @return array
     * @throws \Random\RandomException
     */
    private function buildFromPayment(int $paymentId): array
    {
        $payment = PaymentModel::query()
            ->with([
                'invoices.items',
                'invoices.period',
                'client.dui.document_type',
                'client.nit.document_type',
                'client.address',
                'client.mobile',
                'client.email',
            ])
            ->where('id', $paymentId)
            ->where('status_id', true)
            ->firstOrFail();

        [$body, $gravado, $iva] = $this->buildBodyFromInvoices($payment->invoices);

        return [
            'identificacion' => $this->identificacion(DocumentTypes::FACTURA),
            'documentoRelacionado' => null,
            'emisor' => $this->emisor(),
            'receptor' => $this->buildReceptor($payment->client),
            'otrosDocumentos' => null,
            'ventaTercero' => null,
            'cuerpoDocumento' => $body,
            'resumen' => $this->buildResumen($gravado, $iva, 1, 1),
        ];
    }

    /***
     * Itera las facturas y sus ítems para construir el cuerpo del documento.
     *
     * @param $invoices
     * @return array
     */
    private function buildBodyFromInvoices($invoices): array
    {
        $numItem = 1;
        $gravado = 0;
        $iva = 0;
        $body = [];

        foreach ($invoices as $invoice) {
            $period = $invoice->period->name;

            foreach ($invoice->items as $item) {
                $lineTotal = $this->round2($item->total);
                $lineIva = $this->round2($item->iva);

                $body[] = [
                    'numItem' => $numItem++,
                    'tipoItem' => 2,
                    'numeroDocumento' => null,
                    'cantidad' => $item->quantity ?? 1,
                    'codigo' => null,
                    'codTributo' => null,
                    'uniMedida' => 99,
                    'descripcion' => "{$item->description} ({$period})",
                    'precioUni' => $this->round2($item->total),
                    'montoDescu' => 0,
                    'ventaNoSuj' => 0,
                    'ventaExenta' => 0,
                    'ventaGravada' => $lineTotal,
                    'tributos' => null,
                    'psv' => 0,
                    'noGravado' => 0,
                    'ivaItem' => $lineIva,
                ];

                $gravado += $lineTotal;
                $iva += $lineIva;
            }
        }

        return [$body, $this->round2($gravado), $this->round2($iva)];
    }

    /***
     * Escenario 2 - Desde datos ingresados manualmente
     *
     * Construye el DTE a partir de un JSON manual con sus ítems explícitos
     *
     * @param array $data
     * @return array
     * @throws \Random\RandomException
     */
    private function buildFromManualData(array $data): array
    {
        $client = ClientModel::query()
            ->with([
                'dui.document_type',
                'nit.document_type',
                'address',
                'mobile',
                'email',
            ])
            ->findOrFail($data['client_id']);

        [$body, $gravado, $iva] = $this->buildFromItems($data['items']);

        return [
            'identificacion' => $this->identificacion(DocumentTypes::FACTURA),
            'documentoRelacionado' => null,
            'emisor' => $this->emisor(),
            'receptor' => $this->buildReceptor($client),
            'otrosDocumentos' => null,
            'ventaTercero' => null,
            'cuerpoDocumento' => $body,
            'resumen' => $this->buildResumen($gravado, $iva, (int)$data['payment_condition'], (int)$data['payment_method']),
        ];
    }

    /***
     * Construye el cuerpo del documento a partir del array de ítems del JSON manual.
     *
     * @param array $items
     * @return array
     */
    private function buildFromItems(array $items): array
    {
        $gravado = 0;
        $iva = 0;
        $body = [];

        foreach ($items as $item) {
            $lineTotal = $this->round2($item['total']);
            $lineIva = $this->round2($item['iva']);

            $body[] = [
                'numItem' => $item['_line'],
                'tipoItem' => $item['item_type'],
                'numeroDocumento' => null,
                'cantidad' => $item['quantity'],
                'codigo' => null,
                'codTributo' => null,
                'uniMedida' => 99,
                'descripcion' => $item['description'],
                'precioUni' => $this->round2($item['unit_price']),
                'montoDescu' => 0,
                'ventaNoSuj' => 0,
                'ventaExenta' => 0,
                'ventaGravada' => $lineTotal,
                'tributos' => null,
                'psv' => 0,
                'noGravado' => 0,
                'ivaItem' => $lineIva,
            ];

            $gravado += $lineTotal;
            $iva += $lineIva;
        }

        return [$body, $this->round2($gravado), $this->round2($iva)];
    }

    /***
     * Crea contenido del apartado receptor a partir de un cliente
     *
     * @param $client
     * @return array
     */
    private function buildReceptor($client): array
    {
        $document = $client->dui ?? $client->nit;
        return [
            'tipoDocumento' => $document?->document_type?->code,
            'numDocumento' => $this->parseNumber($document->number ?? null),
            'nrc' => null,
            'nombre' => "{$client->name} {$client->surname}",
            'codActividad' => null,
            'descActividad' => null,
            'direccion' => [
                'departamento' => $client->address?->state_id,
                'municipio' => $client->address?->municipality_id,
                'complemento' => $client->address?->address,
            ],
            'telefono' => $this->phoneFormatter($client->mobile->number ?? null),
            'correo' => $client->email?->email,
        ];
    }

    /***
     * Construye elementos del apartado resumen a partir de los totales calculados.
     *
     * @param float $gravado
     * @param float $iva
     * @return array
     */
    private function buildResumen(float $gravado, float $iva, int $condition, int $method): array
    {
        return [
            'totalNoSuj' => 0,
            'totalExenta' => 0,
            'totalGravada' => $gravado,
            'subTotalVentas' => $gravado,
            'descuNoSuj' => 0,
            'descuExenta' => 0,
            'descuGravada' => 0,
            'porcentajeDescuento' => 0,
            'totalDescu' => 0,
            'tributos' => [],
            'subTotal' => $gravado,
            'ivaRete1' => 0,
            'reteRenta' => 0,
            'montoTotalOperacion' => $gravado,
            'totalNoGravado' => 0,
            'totalPagar' => $gravado,
            'totalLetras' => $this->numberToLetter->convert($gravado),
            'totalIva' => $this->round2($iva),
            'saldoFavor' => 0,
            'condicionOperacion' => $condition,
            'pagos' => [
                'codigo' => $this->paymentMethodCode($method),
                'montoPago' => $gravado,
                'referencia' => null,
                'plazo' => null,
                'periodo' => null,
            ],
            'numPagoElectronico' => null,
        ];
    }

    /***
     * Retorna el código del método de pago.
     *
     * @param int $methodId
     * @return string
     */
    private function paymentMethodCode(int $methodId): string
    {
        $query = PaymentMethodModel::query()->findOrFail($methodId);
        return $query->code;
    }
}
