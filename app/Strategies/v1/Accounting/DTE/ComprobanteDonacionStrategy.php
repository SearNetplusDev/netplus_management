<?php

namespace App\Strategies\v1\Accounting\DTE;

use App\Contracts\v1\Accounting\DTE\DTEGeneratorInterface;
use App\Enums\v1\Billing\DocumentTypes;
use App\Libraries\Accounting\DTE\HeaderUtils;
use App\Libraries\Accounting\DTE\IssuerUtils;
use App\Libraries\NumberToLetter;

class ComprobanteDonacionStrategy implements DTEGeneratorInterface
{
    /***
     * @param HeaderUtils $header
     * @param IssuerUtils $issuer
     * @param NumberToLetter $numberToLetter
     */
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
                'tipoDte' => DocumentTypes::COMPROBANTE_DONACION->code(),
                'numeroControl' => $this->header->controlNumber(DocumentTypes::COMPROBANTE_DONACION),
                'codigoGeneracion' => $this->header->generationCode(),
                'tipoModelo' => 1,
                'tipoOperacion' => 1,
                'tipoContingencia' => null,
                'motivoContingencia' => null,
                'fecEmi' => $this->header->getDate(),
                'horEmi' => $this->header->getHour(),
                'tipoMoneda' => $this->header->getCurrency(),
            ],
            'donante' => [
                'tipoDocumento' => '36',
                'numDocumento' => $this->issuer->getNit(),
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
            'donatario' => [
                'tipoDocumento' => '13',
                'numDocumento' => '000000000',
                'nrc' => '00000000',
                'nombre' => 'John Doe',
                'codActividad' => '10002',
                'descActividad' => 'Pensionado',
                'direccion' => [
                    'departamento' => '06',
                    'municipio' => '24',
                    'complemento' => 'En algún lugar de Panchimalco.',
                ],
                'telefono' => '22832509',
                'correo' => 'johndoe@gmail.com',
                'codDomiciliado' => 2,
                'codPais' => 'SV',
            ],
            'otrosDocuments' => [
                [
                    'codDocAsociado' => '1', // 1. Emisor, 2. Receptor,
                    'descDocumento' => 'Identificación del documento.',  // string <= 100
                    'detalleDocumento' => 'Descripción del documento asociado.',  // string <= 300
                ],
            ],
            'cuerpoDocumento' => [
                [
                    'numItem' => 1,
                    'tipoDonacion' => 1, // 1. Efectivo, 2. Bien, 3. Servicio
                    'cantidad' => 1,
                    'codigo' => null,
                    'uniMedida' => 99,
                    'descripcion' => 'Donación',
                    'depreciacion' => 0,
                    'valorUni' => 100.00,
                    'valor' => 100.00,
                ],
            ],
            'resumen' => [
                'valorTotal' => 100.00,
                'totalLetras' => $this->numberToLetter->convert(100.00),
                //  Puede ser null
                'pagos' => [
                    [
                        'codigo' => '03',   // Tipo DTE || 99 || null
                        'montoPago' => 100.00,
                        'referencia' => null,   //  string <= 50
                    ],
                ],
            ],
            'apendice' => null,
        ];
    }
}
