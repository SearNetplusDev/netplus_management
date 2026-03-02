<?php

namespace App\Strategies\v1\Accounting\DTE;

use App\Contracts\v1\Accounting\DTE\DTEGeneratorInterface;
use App\Enums\v1\Billing\DocumentTypes;
use App\Libraries\Accounting\DTE\HeaderUtils;
use App\Libraries\Accounting\DTE\IssuerUtils;
use App\Libraries\NumberToLetter;

class FacturaExportacionStrategy implements DTEGeneratorInterface
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
                'tipoDte' => DocumentTypes::FACTURA_EXPORTACION->code(),
                'numeroControl' => $this->headerUtils->controlNumber(DocumentTypes::FACTURA_EXPORTACION),
                'codigoGeneracion' => $this->headerUtils->generationCode(),
                'tipoModelo' => 1,  //  1. Modelo Previo, 2. Modelo Diferido
                'tipoOperacion' => 1, //  1. Normal, 2. Contingencia
                'tipoContingencia' => null, // 1. MH caído, 2. ISP caído, 3. Proveedor Caído, 4. Sin energía eléctrica, 5. Otro [string <= 500]
                'motivoContigencia' => null,
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
                'tipoItemExpor' => 2, //  1. Bienes, 2. Servicios, 3. Bienes y Servicios, 4. Otros
                'recintoFiscal' => null,
                'regimen' => null,
            ],
            'receptor' => [
                'nombre' => 'John Doe',
                'tipoDocumento' => '03',
                'numDocumento' => 'A00000000',
                'nombreComercial' => 'John Doe',
                'codPais' => 'CR',
                'nombrePais' => 'Costa Rica',
                'complemento' => 'Refugio de vida salvaje Tucán Home.',
                'tipoPersona' => 1,
                'descActividad' => 'Cuidado de animales en peligro.',
                'telefono' => '50688880000',
                'correo' => 'johndoe@gmail.com',
            ],
            'otrosDocumentos' => null,
            'ventaTercero' => null,
            'cuerpoDocumento' => [
                [
                    'numItem' => 1,
                    'cantidad' => 1,
                    'codigo' => null,
                    'uniMedida' => 99,
                    'descripcion' => 'Algo de algo',
                    'precioUni' => 1253.49,
                    'montoDescu' => 0,
                    'ventaGravada' => 1253.49,
                    'tributos' => null,
                    'noGravado' => 0
                ],
            ],
            'resumen' => [
                'totalGravada' => 1253.49,
                'descuento' => 0,
                'porcentajeDescuento' => 0,
                'totalDescu' => 0,
                'seguro' => null,
                'flete' => null,
                'montoTotalOperacion' => 1253.49,
                'totalNoGravado' => 0,
                'totalPagar' => 1253.49,
                'totalLetras' => $this->numberToLetter->convert(1253.49),
                'condicionOperacion' => 1,
                'pagos' => [
                    'codigo' => '',
                    'montoPago' => 1253.49,
                    'referencia' => null,
                    'plazo' => null,
                    'periodo' => null,
                ],
                'codIncoterms' => null,
                'descIncoterms' => null,
                'numPagoElectronico' => null,
                'observaciones' => null,
            ],
            'apendice' => null,
        ];
    }
}
