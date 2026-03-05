<?php

namespace App\Strategies\v1\Accounting\DTE;

use App\Enums\v1\Billing\DocumentTypes;

class FacturaSujetoExcluidoStrategy extends BaseDTEStrategy
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
            'tipoMoneda' => true,
        ];
    }

    /***
     * @return array
     */
    protected function emisorSchema(): array
    {
        return [
            'nit' => true,
            'nrc' => true,
            'nombre' => true,
            'codActividad' => true,
            'descActividad' => true,
            'nombreComercial' => false,
            'tipoEstablecimiento' => false,
            'direccion' => true,
            'telefono' => true,
            'codEstableMH' => true,
            'codEstable' => true,
            'codPuntoVentaMH' => true,
            'codPuntoVenta' => true,
            'correo' => true,
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
            'identificacion' => $this->identificacion(DocumentTypes::FACTURA_SUJETO_EXCLUIDO),
            'emisor' => $this->emisor(),
            'sujetoExcluido' => [
                'tipoDocumento' => '13',
                'numDocumento' => '000000000',
                'nombre' => 'John Doe',
                'codActividad' => null,
                'descActividad' => null,
                'direccion' => [
                    'departamento' => 12,
                    'municipio' => 22,
                    'complemento' => 'En algún lugar de San Miguel',
                ],
                'telefono' => '73780050',
                'corrreo' => 'johndoe@gmail.com',
            ],
            'cuerpoDocumento' => [
                [
                    'numItem' => 1,
                    'tipoItem' => 2,
                    'cantidad' => 1,
                    'codigo' => null,
                    'uniMedida' => 99,
                    'descripcion' => 'Compra de churros',
                    'precioUni' => 10.00,
                    'montoDescu' => 0,
                    'compra' => 10.00
                ]
            ],
            'resumen' => [
                'totalCompra' => 10.00,
                'descu' => 0,
                'totalDescu' => 0,
                'subTotal' => 10.00,
                'ivaRete1' => 0,
                'reteRenta' => 0,
                'totalPagar' => 10.00,
                'totalLetras' => $this->numberToLetter->convert(10.00),
                'condicionOperacion' => 1,
                'pagos' => [
                    [
                        'codigo' => '01',
                        'montoPago' => 10.00,
                        'referencia' => null,
                        'plazo' => null,
                        'periodo' => null,
                    ]
                ],
                'observaciones' => null,
            ],
            'apendice' => null,
        ];
    }
}
