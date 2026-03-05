<?php

namespace App\Strategies\v1\Accounting\DTE;

use App\Enums\v1\Billing\DocumentTypes;

class FacturaStrategy extends BaseDTEStrategy
{
    /***
     * @return true[]
     */
    protected function identificacionSchema(): array
    {
        return [
            'version' => true,
            'ambiente' => true,
            'tipoDte' => true,
            'numeroControl' => true,
            'codigoGeneracion' => true,
            'tipoModelo' => true,
            'tipoOperacion' => true,
            'fecEmi' => true,
            'horEmi' => true,
            'tipoMoneda' => true,
            'tipoContingencia' => true,
            'motivoContin' => true,
        ];
    }

    /***
     * @param array $data
     * @return array
     * @throws \Random\RandomException
     */
    protected function buildBody(array $data): array
    {
        return [
            'identificacion' => $this->identificacion(DocumentTypes::FACTURA),
            'documentoRelacionado' => null,
            'emisor' => $this->emisor(),
            'receptor' => [
                'tipoDocumento' => 13,
                'numDocumento' => '012564193',
                'nrc' => null,
                'nombre' => 'John Doe',
                'codActividad' => null,
                'descActividad' => null,
                'direccion' => [
                    'departamento' => 12,
                    'municipio' => 22,
                    'complemento' => 'Col. Ciudad Pacifíca.',
                ],
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
