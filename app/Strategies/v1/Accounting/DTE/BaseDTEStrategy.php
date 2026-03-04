<?php

namespace App\Strategies\v1\Accounting\DTE;

use App\Contracts\v1\Accounting\DTE\DTEGeneratorInterface;
use App\Enums\v1\Billing\DocumentTypes;
use App\Libraries\Accounting\DTE\HeaderUtils;
use App\Libraries\Accounting\DTE\IssuerUtils;
use App\Libraries\NumberToLetter;

abstract class BaseDTEStrategy implements DTEGeneratorInterface
{
    /***
     * @param HeaderUtils $headerUtils
     * @param IssuerUtils $issuerUtils
     * @param NumberToLetter $numberToLetter
     */
    public function __construct(
        protected HeaderUtils    $headerUtils,
        protected IssuerUtils    $issuerUtils,
        protected NumberToLetter $numberToLetter,
    )
    {

    }

    /***
     * @param array $data
     * @return array
     */
    public function generate(array $data): array
    {
        return $this->buildBody($data);
    }

    /***
     * @param array $data
     * @return array
     */
    abstract protected function buildBody(array $data): array;

    /***
     * @return array
     */
    protected function emisorFieldMap(): array
    {
        return [];
    }


    /***
     * Campo identificación del DTE
     * @param DocumentTypes $type
     * @param int $version
     * @return array
     * @throws \Random\RandomException
     */
    protected function identificacion(DocumentTypes $type, int $version = 1): array
    {
        return [
            'version' => $version,
            'ambiente' => '00',
            'tipoDte' => $type->code(),
            'numeroControl' => $this->headerUtils->controlNumber($type),
            'codigoGeneracion' => $this->headerUtils->generationCode(),
            'tipoModelo' => 1,
            'tipoOperacion' => 1,
            'fecEmi' => $this->headerUtils->getDate(),
            'horEmi' => $this->headerUtils->getHour(),
            'tipoMoneda' => $this->headerUtils->getCurrency(),
            'tipoContingencia' => null,
            'motivoContin' => null,
        ];
    }

    /***
     * Elementos del campo emisor del DTE.
     * @return array
     */
    protected function emisorBase(): array
    {
        return [
            'nit' => $this->issuerUtils->getNit(),
            'nrc' => $this->issuerUtils->getNrc(),
            'nombre' => $this->issuerUtils->getName(),
            'codActividad' => $this->issuerUtils->activityCode(),
            'descActividad' => $this->issuerUtils->activityName(),
            'nombreComercial' => $this->issuerUtils->getName(),
            'tipoEstablecimiento' => '02',
            'direccion' => [
                'departamento' => $this->issuerUtils->getState(),
                'municipio' => $this->issuerUtils->getMunicipality(),
                'complemento' => $this->issuerUtils->getAddress(),
            ],
            'telefono' => $this->issuerUtils->getPhoneNumber(),
            'correo' => $this->issuerUtils->getEmail(),
            'codEstableMH' => null,
            'codEstable' => null,
            'codPuntoVentaMH' => null,
            'codPuntoVenta' => null,
        ];
    }

    protected function emisor(): array
    {
        return $this->mapFields($this->emisorBase(), $this->emisorFieldMap());
    }

    /***
     * Mapeo dinámico para reemplazar campos en Emisor.
     * @param array $data
     * @param array $map
     * @return array
     */
    protected function mapFields(array $data, array $map): array
    {
        $newData = [];
        foreach ($data as $key => $value) {
            if (isset($map[$key])) {
                $newData[$map[$key]] = $value;
            } else {
                $newData[$key] = $value;
            }
        }
        return $newData;
    }
}
