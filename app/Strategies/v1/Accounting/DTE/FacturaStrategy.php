<?php

namespace App\Strategies\v1\Accounting\DTE;

use App\Contracts\v1\Accounting\DTE\DTEGeneratorInterface;
use App\Enums\v1\Billing\DocumentTypes;
use App\Libraries\Accounting\DTE\HeaderUtils;
use App\Libraries\Accounting\DTE\IssuerUtils;
use App\Libraries\NumberToLetter;

class FacturaStrategy implements DTEGeneratorInterface
{
    public function __construct(
        private HeaderUtils    $header,
        private IssuerUtils    $issuer,
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
            'otrosDocumentos' => null,
            'ventaTercero' => null,
            'cuerpoDocumento' => [
                [
                    'numItem' => 1,
                    'tipoItem' => 2,
                    'numeroDocumento' => null,
                    'cantidad' => 1,
                    'codigo' => null,
                    'codTributo' => null,
                    'uniMedida' => 99,
                    'descripcion' => '35 Mbps + 2 EQM - Enero 2026',
                    'precioUni' => 40.00,
                    'montoDescu' => 0,
                    'ventaNoSuj' => 0,
                    'ventaExenta' => 0,
                    'ventaGravada' => 40.00,
                    'tributos' => null,
                    'psv' => 0,
                    'noGravado' => 0,
                    'ivaItem' => 4.60,
                ],
                [
                    'numItem' => 2,
                    'tipoItem' => 2,
                    'numeroDocumento' => null,
                    'cantidad' => 1,
                    'codigo' => null,
                    'codTributo' => null,
                    'uniMedida' => 99,
                    'descripcion' => '35 Mbps + 2 EQM - Febrero 2026',
                    'precioUni' => 40.00,
                    'montoDescu' => 0,
                    'ventaNoSuj' => 0,
                    'ventaExenta' => 0,
                    'ventaGravada' => 40.00,
                    'tributos' => null,
                    'psv' => 0,
                    'noGravado' => 0,
                    'ivaItem' => 4.60,
                ],
            ],
            'resumen' => [
                'totalNoSuj' => 0,
                'totalExenta' => 0,
                'totalGravada' => 80.00,
                'subTotalVentas' => 80.00,
                'descuNoSuj' => 0,
                'descuExenta' => 0,
                'descuGravada' => 0,
                'porcentajeDescuento' => 0,
                'totalDescu' => 0,
                'tributos' => [],
                'subTotal' => 80.00,
                'ivaRete1' => 0,
                'reteRenta' => 0,
                'montoTotalOperacion' => 80.00,
                'totalNoGravado' => 0,
                'totalPagar' => 80.00,
                'totalLetras' => $this->numberToLetter->convert(80.00),
                'totalIva' => 9.20,
                'saldoFavor' => 0,
                'condicionOperacion' => 1,
                'pagos' => [
                    'codigo' => '01',
                    'montoPago' => 80.00,
                    'referencia' => null,
                    'plazo' => null,
                    'periodo' => null,
                ],
                'numPagoElectronico' => null,
            ],
            'extension' => null,
            'apendice' => null,
        ];
    }
}
