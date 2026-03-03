<?php

namespace App\Strategies\v1\Accounting\DTE;

use App\Contracts\v1\Accounting\DTE\DTEGeneratorInterface;
use App\Enums\v1\Billing\DocumentTypes;
use App\Libraries\Accounting\DTE\HeaderUtils;
use App\Libraries\Accounting\DTE\IssuerUtils;
use App\Libraries\NumberToLetter;

class NotaRemisionStrategy implements DTEGeneratorInterface
{
    public function __construct(
        private HeaderUtils    $headerUtils,
        private IssuerUtils    $issuerUtils,
        private NumberToLetter $numberToLetter,
    )
    {

    }

    /***
     * @param array $data
     * @return array[]
     * @throws \Random\RandomException
     */
    public function generate(array $data): array
    {
        return $this->buildBody($data);
    }

    /***
     * @param array $data
     * @return array[]
     * @throws \Random\RandomException
     */
    private function buildBody(array $data): array
    {
        return [
            'identificacion' => [
                'version' => 3,
                'ambiente' => '01',
                'tipoDte' => DocumentTypes::NOTA_REMISION->code(),
                'numeroControl' => $this->headerUtils->controlNumber(DocumentTypes::NOTA_REMISION),
                'codigoGeneracion' => $this->headerUtils->generationCode(),
                'tipoModelo' => 1,
                'tipoOperacion' => 1,
                'tipoContingencia' => null,
                'motivoContin' => null,
                'fecEmi' => $this->headerUtils->getDate(),
                'horEmi' => $this->headerUtils->getHour(),
                'tipoMoneda' => $this->headerUtils->getCurrency(),
            ],
            'documentoRelacionado' => [
                [
                    'tipoDocumento' => '03',    // 01. Factura de consumidor final, 03. Crédito fiscal
                    'tipoGeneracion' => 2,   // 1. Físico, 2. Electrónico
                    'numeroDocumento' => 'DTE-03-NTPS2026-000000000000153',
                    'fechaEmision' => '2026-01-16',
                ],
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
                'tipoDocumento' => '13',
                'numDocumento' => '123456789',
                'nrc' => null,
                'nombre' => 'John Doe',
                'codActividad' => null,
                'descActividad' => null,
                'nombreComercial' => 'John Doe',
                'direccion' => [
                    'departamento' => $this->issuerUtils->getState(),
                    'municipio' => $this->issuerUtils->getMunicipality(),
                    'complemento' => 'En algún lugar de Moncagua',
                ],
                'telefono' => '79784513',
                'correo' => 'johndoe@gmail.com',
                'bienTitulo' => 'Ni idea',
            ],
            'ventaTercero' => null,
            'cuerpoDocumento' => [
                [
                    'numItem' => 1,
                    'tipoItem' => 2,
                    'numeroDocumento' => 'DTE-03-NTPS2026-000000000000153',
                    'codigo' => null,
                    'codTributo' => null,
                    'descripcion' => 'Algo de algo',
                    'cantidad' => 1,
                    'uniMedida' => 99,
                    'precioUni' => 176.99,
                    'montoDescu' => 0,
                    'ventaNoSuj' => 0,
                    'ventaExenta' => 0,
                    'ventaGravada' => 176.99,
                    'tributos' => ['20'],
                ],
            ],
            'resumen' => [
                'totalNoSuj' => 0,
                'totalExenta' => 0,
                'totalGravada' => 176.99,
                'subTotalVentas' => 176.99,
                'descuNoSuj' => 0,
                'descuExenta' => 0,
                'descuGravada' => 0,
                'porcentajeDescuento' => 0,
                'totalDescu' => 0,
                'tributos' => [
                    'codigo' => '20',
                    'descripcion' => 'Impuesto al valor agregado 13%',
                    'valor' => 23.01,
                ],
                'subTotal' => 176.99,
                'montoTotalOperacion' => 200.00,
                'totalLetras' => $this->numberToLetter->convert(200.00),
            ],
            'extension' => null,
            'apendice' => null,
        ];
    }
}
