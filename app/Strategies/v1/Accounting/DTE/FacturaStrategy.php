<?php

namespace App\Strategies\v1\Accounting\DTE;

use App\Contracts\v1\Accounting\DTE\DTEGeneratorInterface;
use App\Enums\v1\Billing\DocumentTypes;
use App\Libraries\Accounting\DTE\IssuerUtils;
use App\Libraries\Accounting\HeaderUtils;

class FacturaStrategy implements DTEGeneratorInterface
{
    public function __construct(
        private HeaderUtils $header,
        private IssuerUtils $issuer,
    )
    {
    }

    /***
     * @param array $data
     * @return array
     * @throws \Random\RandomException
     */
    public function generate(array $data): array
    {
        return $this->buildBody($data);
    }

    /***
     * Crea el json con los datos del DTE.
     * @param array $data
     * @return array
     * @throws \Random\RandomException
     */
    private function buildBody(array $data): array
    {
        return [
            'identificacion' => [
                'version' => 1,
                'ambiente' => $this->header->ambient(),
                'tipoDte' => DocumentTypes::FACTURA->code(),
                'numeroControl' => $this->header->controlNumber(DocumentTypes::FACTURA),
                'codigoGeneracion' => $this->header->generationCode(),
                'tipoModelo' => 1,
                'tipoOperacion' => 1,
                'tipoContingencia' => null,
                'motivoContin' => null,
                'fecEmi' => $this->header->getDate(),
                'horEmi' => $this->header->getHour(),
                'tipoMoneda' => $this->header->getCurrency(),
            ],
            'documentoRelacionado' => null,
            'emisor' => [
                'nit' => $this->issuer->getNit(),
                'nrc' => $this->issuer->getNrc(),
                'nombre' => $this->issuer->getName(),
                'codActividad' => $this->issuer->activityCode(),
                'descActividad' => $this->issuer->activityName(),
                'nombreComercial' => $this->issuer->getName(),
                'tipoEstablecimiento' => '02',
                'direccion' => [
                    'departamento' => $this->issuer->getState(),
                    'municipio' => $this->issuer->getMunicipality(),
                    'complemento' => $this->issuer->getAddress(),
                ],
                'telefono' => $this->issuer->getPhoneNumber(),
                'correo' => $this->issuer->getEmail(),
                'codEstableMH' => null,
                'codEstable' => null,
                'codPuntoVentaMH' => null,
                'codPuntoVenta' => null,
            ],
            'receptor' => [
                'tipoDocumento' => null,
                'numDocumento' => null,
                'nrc' => null,
                'nombre' => null,
                'codActividad' => null,
                'descActividad' => null,
                'direccion' => null,
                'telefono' => null,
                'correo' => null,
            ],
        ];
    }
}
