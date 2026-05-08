<?php

namespace App\Strategies\v1\Accounting\DTE;

use App\Enums\v1\Accounting\TaxRate;
use App\Enums\v1\Billing\DocumentTypes;
use App\Enums\v1\Clients\ClientTypes;
use App\Models\Accounting\DTEModel;
use App\Models\Clients\ClientModel;
use Carbon\Carbon;
use Random\RandomException;
use Throwable;

class NotaDebitoStrategy extends BaseDTEStrategy
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
     * @return false[]
     */
    protected function emisorSchema(): array
    {
        return [
            'codEstableMH' => false,
            'codEstable' => false,
            'codPuntoVentaMH' => false,
            'codPuntoVenta' => false,
        ];
    }

    /***
     * Carga datos del cliente y construye el DTE.
     *
     * @param array $data
     * @return array
     * @throws RandomException
     * @throws Throwable
     */
    protected function buildBody(array $data): array
    {
        $client = $this->loadClient((int)$data['client_id'], [
            'corporate_info.activity',
            'corporate_info.state',
            'corporate_info.municipality',
            'nit',
            'mobile',
            'email',
        ]);
        $this->canGenerate($client);
        $relatedDocId = $data['related_documents'][0]['document_number'];
        $relatedDoc = $this->getRelatedDoc($relatedDocId);
        $retainedIva = (($data['totals']['iva_retenido'] ?? 0) > 0) || $client->corporate_info?->retained_iva ?? false;
        $discount = (float)$data['totals']['discount'] ?? 0;
        [$body, $gravado] = $this->buildLinesFromItems(items: $data['items'], relatedDoc: $relatedDoc->control_number);

        return $this->assembleDocument(
            clientModel: $client,
            DTEModel: $relatedDoc,
            body: $body,
            gravado: $gravado,
            retainedIva: $retainedIva,
            discount: $discount,
            condition: (int)$data['payment_condition'],
        );
    }

    /***
     * Política que valida que se cumplan las condiciones para generar la nota de débito.
     *
     * @param ClientModel $clientModel
     * @return void
     */
    private function canGenerate(ClientModel $clientModel): void
    {
        $isValidClientType = $clientModel->client_type_id === ClientTypes::CORPORATE->value;
        $isValidDocumentType = $clientModel->document_type_id === DocumentTypes::NOTA_DEBITO->value;
        $hasCorporateInfo = $clientModel->relationLoaded('corporate_info')
            ? $clientModel->corporate_info !== null
            : $clientModel->corporate_info()->exists();

        if (!$isValidClientType && !$isValidDocumentType && !$hasCorporateInfo) {
            throw new  \InvalidArgumentException('Este cliente no cumple con los requisitos para generarle una ' . DocumentTypes::NOTA_DEBITO->label());
        }
    }

    /***
     * Construye el bloque 'cuerpoDocumento'
     *
     * @param array $items
     * @param string $relatedDoc
     * @return array
     */
    private function buildLinesFromItems(array $items, string $relatedDoc): array
    {
        $gravado = 0;
        $body = [];

        foreach ($items as $item) {
            $unitPrice = (float)$item['unit_price'] / TaxRate::VALOR_NETO->value();
            $gravadoRaw = $unitPrice * (int)$item['quantity'];
            $body[] = [
                'numItem' => (int)$item['_line'],
                'tipoItem' => (int)$item['item_type'],
                'numeroDocumento' => $relatedDoc,
                'cantidad' => (int)$item['quantity'],
                'codigo' => null,
                'codTributo' => null,
                'uniMedida' => 99,
                'descripcion' => $item['description'],
                'precioUni' => $unitPrice,
                'montoDescu' => 0,
                'ventaNoSuj' => 0,
                'ventaExenta' => 0,
                'ventaGravada' => $gravadoRaw,
                'tributos' => ['20'],
            ];

            $gravado += $gravadoRaw;
        }

        return [$body, $gravado];
    }

    /***
     * Construye el bloque 'documentoRelacionado' del DTE.
     *
     * @param DTEModel $DTEModel
     * @return array
     */
    private function buildRelatedDoc(DTEModel $DTEModel): array
    {
        return [
            [
                'tipoDocumento' => $DTEModel->dte_type?->code,
                'tipoGeneracion' => 2,
                'numeroDocumento' => $DTEModel->control_number,
                'fechaEmision' => Carbon::parse($DTEModel->generation_datetime)->format('Y-m-d'),
            ]
        ];
    }

    /***
     * Construye el bloque 'resumen' del DTE.
     *
     * @param float $gravado
     * @param bool $retainedIva
     * @param float $discount
     * @param int $condition
     * @return array
     */
    private function buildResumen(
        float $gravado,
        bool  $retainedIva,
        float $discount,
        int   $condition
    ): array
    {
        $totalConIva = $gravado * TaxRate::VALOR_NETO->value();
        $totales = $this->calculateTotals(
            gravado: $totalConIva,
            discount: $discount,
            retainedIva: $retainedIva
        );

        return [
            'totalNoSuj' => 0,
            'totalExenta' => 0,
            'totalGravada' => $this->round2($totales['neto']),
            'subTotalVentas' => $this->round2($totales['neto']),
            'descuNoSuj' => 0,
            'descuExenta' => 0,
            'descuGravada' => $this->round2($discount),
            'totalDescu' => $this->round2($discount),
            'tributos' => [
                [
                    'codigo' => '20',
                    'descripcion' => 'Impuesto al Valor Agregado 13%',
                    'valor' => $this->round2($totales['iva']),
                ]
            ],
            'subTotal' => $this->round2($totales['neto']),
            'ivaPerci1' => 0,
            'ivaRete1' => $this->round2($totales['ivaRetenido']),
            'reteRenta' => 0,
            'montoTotalOperacion' => $this->round2($totales['totalPagar']),
            'totalLetras' => $this->numberToLetter->convert($this->round2($totales['totalPagar'])),
            'condicionOperacion' => $condition,
            'numPagoElectronico' => null,
        ];
    }

    /***
     * Ensambla la estructura completa del json.
     *
     * @param ClientModel $clientModel
     * @param DTEModel $DTEModel
     * @param array $body
     * @param float $gravado
     * @param bool $retainedIva
     * @param float $discount
     * @param int $condition
     * @return array
     * @throws RandomException
     * @throws Throwable
     */
    private function assembleDocument(
        ClientModel $clientModel,
        DTEModel    $DTEModel,
        array       $body,
        float       $gravado,
        bool        $retainedIva,
        float       $discount,
        int         $condition
    ): array
    {
        return [
            'identificacion' => $this->identificacion(DocumentTypes::NOTA_DEBITO, 3),
            'documentoRelacionado' => $this->buildRelatedDoc($DTEModel),
            'emisor' => $this->emisorBase(),
            'receptor' => $this->buildReceptorBase($clientModel),
            'ventaTercero' => null,
            'cuerpoDocumento' => $body,
            'resumen' => $this->buildResumen(
                gravado: $gravado,
                retainedIva: $retainedIva,
                discount: $discount,
                condition: $condition,
            ),
            'extension' => null,
            'apendice' => null,
        ];
    }
}
