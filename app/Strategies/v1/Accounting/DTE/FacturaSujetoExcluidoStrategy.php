<?php

namespace App\Strategies\v1\Accounting\DTE;

use App\Contracts\v1\Accounting\DTE\DTEGeneratorInterface;
use App\Enums\v1\Billing\DocumentTypes;
use App\Libraries\Accounting\DTE\HeaderUtils;
use App\Libraries\Accounting\DTE\IssuerUtils;
use App\Libraries\NumberToLetter;

class FacturaSujetoExcluidoStrategy implements DTEGeneratorInterface
{

    /***
     * @param HeaderUtils $header
     * @param IssuerUtils $issuer
     * @param NumberToLetter $numberToLetter
     */
    public function __construct(
        private HeaderUtils    $header,
        private IssuerUtils    $issuer,
        private NumberToLetter $numberToLetter
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
                'tipoDte' => DocumentTypes::FACTURA_SUJETO_EXCLUIDO->code(),
                'numeroControl' => $this->header->controlNumber(DocumentTypes::FACTURA_SUJETO_EXCLUIDO),
                'codigoGeneracion' => $this->header->generationCode(),
                'tipoModelo' => 1,
                'tipoOperacion' => 1,
                'tipoContingencia' => null,
                'motivoContin' => null,
                'fecEmi' => $this->header->getDate(),
                'horEmi' => $this->header->getHour(),
                'tipoMoneda' => $this->header->getCurrency(),
            ],
            'emisor' => [
                'nit' => $this->issuer->getNit(),
                'nrc' => $this->issuer->getNrc(),
                'nombre' => $this->issuer->getName(),
                'codActividad' => $this->issuer->activityCode(),
                'descActividad' => $this->issuer->activityName(),
                'direccion' => [
                    'departamento' => $this->issuer->getState(),
                    'municipio' => $this->issuer->getMunicipality(),
                    'complemento' => $this->issuer->getAddress(),
                ],
                'telefono' => $this->issuer->getPhoneNumber(),
                'codEstableMH' => null,
                'codEstable' => null,
                'codPuntoVentaMH' => null,
                'codPuntoVenta' => null,
                'correo' => $this->issuer->getEmail(),
            ],
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
