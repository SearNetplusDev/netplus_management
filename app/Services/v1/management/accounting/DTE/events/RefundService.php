<?php

namespace App\Services\v1\management\accounting\DTE\events;

use App\Enums\v1\Billing\DocumentTypes;
use App\Libraries\Accounting\DTE\HeaderUtils;
use App\Libraries\Accounting\DTE\IssuerUtils;
use App\Libraries\NumberToLetter;
use App\Models\Accounting\DTEModel;
use Carbon\Carbon;

readonly class RefundService
{
    public function __construct(
        private readonly HeaderUtils    $headerUtils,
        private readonly IssuerUtils    $issuerUtils,
        private readonly NumberToLetter $numberToLetter,
    )
    {

    }

    public function apply(int $dteId, int $dteType, array $items): array
    {
        return [
            'identificacion' => $this->buildIdentification(),
            'documentoRelacionado' => [$this->buildRelatedDoc(dteId: $dteId, dteType: $dteType)],
            'emisor' => $this->buildIssuer(),
            'documento' => $this->buildDocument(dteId: $dteId),
            'ventaTercero' => null,
            'compraTercero' => null,
        ];
    }

    /****
     * Construye el bloque 'identificacion' del json.
     *
     * @return array
     * @throws \Random\RandomException
     */
    private function buildIdentification(): array
    {
        return [
            'version' => 1,
            'ambiente' => $this->headerUtils->ambient(),
            'tipoModelo' => 1,
            'tipoOperacion' => 1,
            'tipoEvento' => '18',
            'tipoContingencia' => null,
            'motivoContin' => null,
            'codigoGeneracion' => $this->headerUtils->generationCode(),
            'fecEmi' => $this->headerUtils->getDate(),
            'horEmi' => $this->headerUtils->getHour(),
            'fusion' => null,
            'tipoMoneda' => $this->headerUtils->getCurrency(),
        ];
    }

    /****
     * Construye el bloque 'documentoRelacionado'.
     *
     * @param int $dteId
     * @param int $dteType
     * @return array
     */
    private function buildRelatedDoc(int $dteId, int $dteType): array
    {
        $type = DocumentTypes::from($dteType);
        $dte = $this->getDTEData($dteId);

        return [
            'tipoDocumento' => $type->code(),
            'codigoGeneracion' => $dte->generation_code,
            'fechaEmision' => Carbon::parse($dte->generation_datetime)->toDateString(),
        ];
    }

    /**
     * Construye el bloque 'emisor'.
     *
     * @return array
     */
    private function buildIssuer(): array
    {
        return [
            'nit' => $this->issuerUtils->getNit(),
            'nombre' => $this->issuerUtils->getName(),
            'codEstableMH' => 'To do',
            'codEstable' => null,
            'codPuntoVentaMH' => 'To do',
            'codPuntoVenta' => null,
            'recintoFiscal' => null,
            'tipoRegimen' => null,
            'regimen' => null,
            'tipoItemExpor' => null,
        ];
    }

    private function buildDocument(int $dteId): array
    {
        $dte = $this->getDTEData($dteId);
        $docType = match (true) {
            !empty($dte->client?->dui) => '13',
            !empty($dte->client?->nit) => '36',
            default => null,
        };
        $docNumber = match (true) {
            !empty($dte->client?->dui) => $dte->client?->dui->number,
            !empty($dte->client?->nit) => $dte->client?->nit->number,
            default => null,
        };

        return [
            'tipoDocumento' => $docType,
            'numDocumento' => str_replace('-', '', $docNumber),
            'nombre' => ucwords("{$dte->client?->name} {$dte->client?->surname}"),
            'codPais' => null,
            'nombrePais' => null,
            'telefono' => preg_replace('/[\s-]+/', '', $dte->client?->mobile?->number),
            'correo' => $dte->client?->email?->email,
        ];
    }

    /**
     * Obtiene los datos generales del DTE.
     *
     * @param int $dteId
     * @return DTEModel
     */
    private function getDTEData(int $dteId): DTEModel
    {
        return DTEModel::query()->findOrFail($dteId);
    }
}
