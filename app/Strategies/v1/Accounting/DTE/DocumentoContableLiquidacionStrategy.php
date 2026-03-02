<?php

namespace App\Strategies\v1\Accounting\DTE;

use App\Contracts\v1\Accounting\DTE\DTEGeneratorInterface;
use App\Enums\v1\Billing\DocumentTypes;
use App\Libraries\Accounting\DTE\HeaderUtils;
use App\Libraries\Accounting\DTE\IssuerUtils;
use App\Libraries\NumberToLetter;

class DocumentoContableLiquidacionStrategy implements DTEGeneratorInterface
{
    /***
     * @param HeaderUtils $headerUtils
     * @param IssuerUtils $issuerUtils
     * @param NumberToLetter $numberToLetter
     */
    public function __construct(
        private HeaderUtils    $headerUtils,
        private IssuerUtils    $issuerUtils,
        private NumberToLetter $numberToLetter,
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
     * @param array $data
     * @return array
     * @throws \Random\RandomException
     */
    private function buildBody(array $data): array
    {
        return [
            'identificacion' => [
                'version' => 1,
                'ambiente' => '01',
                'tipoDte' => DocumentTypes::DOCUMENTO_CONTABLE_LIQUIDACION->code(),
                'numeroControl' => $this->headerUtils->controlNumber(DocumentTypes::DOCUMENTO_CONTABLE_LIQUIDACION),
                'codigoGeneracion' => $this->headerUtils->generationCode(),
                'tipoModelo' => 1,
                'tipoOperacion' => 1,
                'fecEmi' => $this->headerUtils->getDate(),
                'horEmi' => $this->headerUtils->getHour(),
                'tipoMoneda' => $this->headerUtils->getCurrency(),
            ],
            'emisor' => [
                'nit' => $this->issuerUtils->getNit(),
                'nrc' => $this->issuerUtils->getNrc(),
                'nombre' => $this->issuerUtils->getName(),
                'codActividad' => $this->issuerUtils->activityCode(),
                'descActividad' => $this->issuerUtils->activityName(),
                'nombreComercial' => $this->issuerUtils->getName(),
                'tipoEstablecimiento' => '02',
                'telefono' => $this->issuerUtils->getPhoneNumber(),
                'correo' => $this->issuerUtils->getEmail(),
                'direccion' => [
                    'departamento' => $this->issuerUtils->getState(),
                    'municipio' => $this->issuerUtils->getMunicipality(),
                    'complemento' => $this->issuerUtils->getAddress(),
                ],
                'codigoMH' => null,
                'codigo' => null,
                'puntoVentaMH' => null,
                'puntoVentaContri' => null,
            ],
            'receptor' => [
                'nit' => '000000000',
                'nrc' => '12345678',
                'nombre' => 'John Doe',
                'codActividad' => '10001',
                'descActividad' => 'Empleado',
                'nombreComercial' => 'John Doe',
                'tipoEstablecimiento' => '02',    // 01. Sucursal, 02. Casa Matriz, 04. Bodega, 07. Patio
                'direccion' => [
                    'departamento' => '09',
                    'municipio' => '11',
                    'complemento' => 'En algún lugar de Dolores.',
                ],
                'telefono' => '78520412',
                'correo' => 'johndoe@gmail.com',
                'codigoMH' => null,
                'puntoVentaMH' => null,
            ],
            'cuerpoDocumento' => [
                'periodoLiquidacionFechaInicio' => '2026-01-19',
                'periodoLiquidacionFechaFin' => '2026-03-19',
                'codLiquidacion' => null,
                'cantidadDoc' => 1,
                'valorOperaciones' => 0,
                'montoSinPercepcion' => 0,
                'descripSinPercepcion' => 0,
                'subTotal' => 0,
                'iva' => 0,
                'montoSujetoPercepcion' => 0,
                'ivaPercibido' => 0,
                'comision' => 0,
                'porcentComision' => null,
                'ivaComision' => 0,
                'liquidoApagar' => 0,
                'totalLetras' => $this->numberToLetter->convert(0.00),
                'observaciones' => null,
            ],
            'extension' => null,
            'apendice' => null,
        ];
    }
}
