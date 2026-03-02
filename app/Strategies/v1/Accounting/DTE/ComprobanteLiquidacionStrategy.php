<?php

namespace App\Strategies\v1\Accounting\DTE;

use App\Contracts\v1\Accounting\DTE\DTEGeneratorInterface;
use App\Enums\v1\Billing\DocumentTypes;
use App\Libraries\Accounting\DTE\HeaderUtils;
use App\Libraries\Accounting\DTE\IssuerUtils;
use App\Libraries\NumberToLetter;

class ComprobanteLiquidacionStrategy implements DTEGeneratorInterface
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
                'tipoDte' => DocumentTypes::COMPROBANTE_LIQUIDACION->code(),
                'numeroControl' => $this->headerUtils->controlNumber(DocumentTypes::COMPROBANTE_LIQUIDACION),
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
            ],
            'receptor' => [
                'nit' => '12052302951012',
                'nrc' => '12345678',
                'nombre' => 'John Doe',
                'codActividad' => '10001',
                'descActividad' => 'Empleados',
                'nombreComercial' => null,
                'direccion' => [
                    'departamento' => 12,
                    'municipio' => 23,
                    'complemento' => 'En algún lugar de San Jorge.',
                ],
                'telefono' => '73780050',
                'correo' => 'jonhdoe@gmail.com',
            ],
            'cuerpoDocumento' => [
                [
                    'numItem' => 1,
                    'tipoDte' => '01',    // 01, 03, 05, 06, 11
                    'tipoGeneracion' => 2,    // 1. Físico, 2. Electrónico
                    'numeroDocumento' => 'DTE-01-NTPS2026-000000000000345',
                    'fechaGeneracion' => '2026-01-15',
                    'ventaNoSuj' => 0,
                    'ventaExenta' => 0,
                    'ventaGravada' => 0,
                    'exportaciones' => 0,
                    'tributos' => [null],   // 20, C3, 59, 71, D1, C8, D5, D4
                    'ivaItem' => 0,
                    'obsItem' => 'Something',   // string > 3 && <= 3000
                ],
            ],
            'resumen' => [
                'totalNoSuj' => 0,
                'totalExenta' => 0,
                'totalGravada' => 0,
                'totalExportacion' => 0,
                'subTotalVentas' => 0,
                'tributos' => [null],     // 20, C3, 59, 71, D1, C8, D5, D4
                'montoTotalOperacion' => 0,
                'ivaPerci' => 0,
                'total' => 0,
                'totalLetras' => $this->numberToLetter->convert(0.00),
                'condicionOperacion' => 1,   // 1. Contado, 2. Crédito, 3. Otro
            ],
            'extension' => null,
            'apendice' => null,
        ];
    }
}
