<?php

namespace App\Strategies\v1\Accounting\DTE;

use App\Contracts\v1\Accounting\DTE\DTEGeneratorInterface;
use App\Enums\v1\Billing\DocumentTypes;
use App\Libraries\Accounting\DTE\HeaderUtils;
use App\Libraries\Accounting\DTE\IssuerUtils;
use App\Libraries\NumberToLetter;

class CreditoFiscalStrategy implements DTEGeneratorInterface
{
    public function __construct(
        private HeaderUtils    $header,
        private IssuerUtils    $issuer,
        private NumberToLetter $numberToLetter,
    )
    {

    }

    public function generate(array $data): array
    {
        return $this->buildBody($data);
    }

    private function buildBody(array $data): array
    {
        return [
            'identificacion' => [
                'version' => 3,
                'ambiente' => '01',
                'tipoDte' => DocumentTypes::CREDITO_FISCAL->code(),
                'numeroControl' => $this->header->controlNumber(DocumentTypes::CREDITO_FISCAL),
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
                'nit' => '036327737',
                'nrc' => '12345678',
                'nombre' => 'John Doe',
                'codActividad' => '33110',
                'descActividad' => 'Reparación y mantenimiento de productos elaborados de metal',
                'nombreComercial' => 'John Doe S.A. DE C.V.',
                'direccion' => [
                    'departamento' => 12,
                    'municipio' => 22,
                    'complemento' => 'En algún lugar de San Miguel',
                ],
                'telefono' => '73780050',
                'correo' => 'johndoe@gmail.com',
            ],
            'otrosDocumentos' => null,
            'ventaTercero' => null,
            'cuerpoDocumento' => [
                [
                    'numItem' => 1,
                    'tipoItem' => 2,
                    'numeroDocumento' => null,
                    'codigo' => null,
                    'codTributo' => null,
                    'descripcion' => '35 Mbps + 2 EQM',
                    'cantidad' => 1,
                    'uniMedida' => 99,
                    'precioUni' => 40.70796460,
                    'montoDescu' => 0,
                    'ventaNoSuj' => 0,
                    'ventaExenta' => 0,
                    'ventaGravada' => 40.70796460,
                    'tributos' => ["20"],
                    'psv' => 0,
                    'noGravado' => 0,
                ],
            ],
            'resumen' => [
                'totalNoSuj' => 0,
                'totalExenta' => 0,
                'totalGravado' => 40.71,
                'subTotalVentas' => 40.71,
                'descuNoSuj' => 0,
                'descuExenta' => 0,
                'descuGravada' => 0,
                'porcentajeDescuento' => 0,
                'totalDescu' => 0,
                'tributos' => [
                    [
                        'codigo' => '20',
                        'descripcion' => 'Impuesto al Valor Agregado 13%',
                        'valor' => 5.29
                    ],
                ],
                'subTotal' => 40.71,
                'ivaPerci1' => 0,
                'ivaRete1' => 0,
                'reteRenta' => 0,
                'montoTotalOperacion' => 46.00,
                'totalNoGravado' => 0,
                'totalPagar' => 46.00,
                'totalLetras' => $this->numberToLetter->convert(46.00),
                'saldoFavor' => 0,
                'condicionOperacion' => 1,
                'pagos' => [
                    [
                        'codigo' => '01',
                        'montoPago' => 46.00,
                        'referencia' => null,
                        'plazo' => null,
                        'periodo' => null,
                    ],
                ],
                'numPagoElectronico' => null,
            ],
            'extension' => null,
            'apendice' => null,
        ];
    }
}
