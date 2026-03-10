<?php

namespace App\Strategies\v1\Accounting\DTE;

use App\Enums\v1\Billing\DocumentTypes;
use App\Models\Billing\PaymentModel;

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
     * @param array $data
     * @return array
     * @throws \Random\RandomException
     */
    protected function buildBody(array $data): array
    {
        $query = PaymentModel::query()
            ->with([
                'invoices.items',
                'invoices.period',
                'client.dui.document_type',
                'client.nit.document_type',
                'client.address',
                'client.mobile',
                'client.email',
            ])
            ->where([
                ['id', $data['payment_id']],
                ['status_id', true],
            ])
            ->firstOrFail();

        [$body, $gravado, $iva] = $this->buildDocumentBody($query->invoices);

        return [
            'identificacion' => $this->identificacion(DocumentTypes::FACTURA),
            'documentoRelacionado' => null,
            'emisor' => $this->emisor(),
            'receptor' => $this->buildReceptor($query->client),
            'otrosDocumentos' => null,
            'ventaTercero' => null,
            'cuerpoDocumento' => $body,
            'resumen' => $this->buildResumen($gravado, $iva),
        ];
    }

    /***
     * Crea contenido del apartado receptor
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
     * Construye el apartado cuerpoDocumento.
     * @param $invoices
     * @return array
     */
    private function buildDocumentBody($invoices): array
    {
        $numItem = 1;
        $gravado = 0;
        $iva = 0;
        $body = [];

        foreach ($invoices as $invoice) {
            $period = $invoice->period->name;

            foreach ($invoice->items as $item) {
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
                    'ventaGravada' => $this->round2($item->total),
                    'tributos' => null,
                    'psv' => 0,
                    'noGravado' => 0,
                    'ivaItem' => $this->round2($item->iva),
                ];
                $gravado += $this->round2($item->total);
                $iva += $this->round2($item->iva);
            }
        }
        return [array_map(fn($i) => $i, $body), $this->round2($gravado), $this->round2($iva)];
    }

    /***
     * Construye elementos del apartado resumen.
     * @param float $gravado
     * @param float $iva
     * @return array
     */
    private function buildResumen(float $gravado, float $iva): array
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
            'condicionOperacion' => 1,
            'pagos' => [
                'codigo' => '01',
                'montoPago' => $gravado,
                'referencia' => null,
                'plazo' => null,
                'periodo' => null,
            ],
            'numPagoElectronico' => null,
        ];
    }
}
