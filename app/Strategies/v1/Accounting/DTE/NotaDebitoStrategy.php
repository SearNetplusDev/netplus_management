<?php

namespace App\Strategies\v1\Accounting\DTE;

use App\Contracts\v1\Accounting\DTE\DTEGeneratorInterface;
use App\Enums\v1\Billing\DocumentTypes;
use App\Libraries\Accounting\DTE\HeaderUtils;
use App\Libraries\Accounting\DTE\IssuerUtils;
use App\Libraries\NumberToLetter;

class NotaDebitoStrategy implements DTEGeneratorInterface
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
                'tipoDte' => DocumentTypes::NOTA_DEBITO->code(),
                'numeroControl' => $this->headerUtils->controlNumber(DocumentTypes::NOTA_DEBITO),
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
                    'tipoDocumento' => '03',    // 03. Crédito fiscal, 07. Comprobante de retención.
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
            ],
            'receptor' => [
                'nit' => '12031910901015',
                'nrc' => '12345678',
                'nombre' => 'John Doe',
                'codActividad' => '10001',
                'descActividad' => 'Empleado',
                'nombreComercial' => 'John Doe',
                'direccion' => [
                    'departamento' => '13',
                    'municipio' => '28',
                    'complemento' => 'En algún lugar de Jocoro',
                ],
                'telefono' => '22832509',
                'correo' => 'johndoe@gmail.com',
            ],
            'ventaTercero' => null,
            'cuerpoDocumento' => [
                [
                    'numItem' => 1,
                    'tipoItem' => 2,
                    'numeroDocumento' => 'DTE-03-NTPS2026-000000000000153',
                    'cantidad' => 1,
                    'codigo' => null,
                    'codTributo' => null,
                    'uniMedida' => 99,
                    'descripcion' => 'Tranzas con un CCF',
                    'precioUni' => 159.59,
                    'montoDescu' => 0,
                    'ventaNoSuj' => 0,
                    'ventaExenta' => 0,
                    'ventaGravada' => 159.59,
                    'tributos' => ['20'],
                ],
            ],
            'resumen' => [
                'totalNoSuj' => 0,
                'totalExenta' => 0,
                'totalGravada' => 159.59,
                'subTotalVentas' => 159.59,
                'descuNoSuj' => 0,
                'descuExenta' => 0,
                'descuGravada' => 0,
                'totalDescu' => 0,
                'tributos' => [
                    'codigo' => '20',
                    'descripcion' => 'Impuesto al valor agregado 13%',
                    'valor' => 20.75,
                ],
                'subTotal' => 159.59,
                'ivaPerci1' => 0,
                'ivaRete1' => 0,
                'reteRenta' => 0,
                'montoTotalOperacion' => 180.34,
                'totalLetras' => $this->numberToLetter->convert(180.34),
                'condicionOperacion' => 1,   // 1. Contado, 2. Crédito, 3. Otros
                'numPagoElectronico' => null
            ],
            'extension' => null,
            'apendice' => null,
        ];
    }
}
