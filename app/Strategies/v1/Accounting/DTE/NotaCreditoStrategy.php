<?php

namespace App\Strategies\v1\Accounting\DTE;

use App\Enums\v1\Billing\DocumentTypes;

class NotaCreditoStrategy extends BaseDTEStrategy
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
            'tipoContingencia' => true,
            'motivoContin' => true,
            'fecEmi' => true,
            'horEmi' => true,
            'tipoMoneda' => true
        ];
    }

    /***
     * @return false[]
     */
    protected function emisorSchema(): array
    {
        return [
            'codEstableMH' => false,
            'codEstable' => false,
            'codPuntoVentaMH' => false,
            'codPuntoVenta' => false,
        ];
    }

    /***
     * @param array $data
     * @return array[]
     * @throws \Random\RandomException
     */
    protected function buildBody(array $data): array
    {
        return [
            'identificacion' => $this->identificacion(DocumentTypes::NOTA_CREDITO, 3),
            'documentoRelacionado' => [
                [
                    'tipoDocumento' => '03',    // 03. Crédito fiscal, 07. Comprobante de retención.
                    'tipoGeneracion' => 2,   // 1. Físico, 2. Electrónico
                    'numeroDocumento' => 'DTE-03-NTPS2026-000000000000153',
                    'fechaEmision' => '2026-01-16',
                ],
            ],
            'emisor' => $this->emisor(),
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
            ],
            'extension' => null,
            'apendice' => null,
        ];
    }
}
