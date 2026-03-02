<?php

namespace App\Strategies\v1\Accounting\DTE;

use App\Contracts\v1\Accounting\DTE\DTEGeneratorInterface;
use App\Enums\v1\Billing\DocumentTypes;
use App\Libraries\Accounting\DTE\HeaderUtils;
use App\Libraries\Accounting\DTE\IssuerUtils;
use App\Libraries\NumberToLetter;

class ComprobanteRetencionStrategy implements DTEGeneratorInterface
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
                'tipoDte' => DocumentTypes::COMPROBANTE_RETENCION->code(),
                'numeroControl' => $this->headerUtils->controlNumber(DocumentTypes::COMPROBANTE_RETENCION),
                'codigoGeneracion' => $this->headerUtils->generationCode(),
                'tipoModelo' => 1,
                'tipoOperacion' => 1,
                'tipoContingencia' => null,
                'motivoContin' => null,
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
                'codigoMH' => null,
                'codigo' => null,
                'puntoVentaMH' => null,
                'puntoVenta' => null,
                'correo' => $this->issuerUtils->getEmail(),
            ],
            'receptor' => [
                'tipoDocumento' => '13',    //  36. NIT, 13. DUI, 37. Otro, 03. Pasaporte, 02. Carnet de residente
                'numDocumento' => '000000002',
                'nrc' => '12345678',
                'nombre' => 'John Doe',
                'codActividad' => '10001',
                'descActividad' => 'Empleado',
                'nombreComercial' => 'John Doe',
                'direccion' => [
                    'departamento' => '05',
                    'municipio' => '27',
                    'complemento' => 'En algún lugar de Tamanique',
                ],
                'telefono' => '79780050',
                'correo' => 'johndoe@gmail.com',
            ],
            'cuerpoDocumento' => [
                [
                    'numItem' => 1,
                    'tipoDte' => '14',  //  01. Factura, 03. Crédito Fiscal, 14. Factura de Sujeto Excluido
                    'tipoDoc' => 1, // 1. Físico, 2. Electrónico
                    'numDocumento' => 'DTE-14-NTPS2026-000000000000005',
                    'fechaEmision' => '2026-02-08',
                    'montoSujetoGrav' => 0,
                    'codigoRetencionMH' => 'C4',    // 22. IVA Retenido 1%, C4. IVA 13%, C9. Otras retenciones de IVA
                    'ivaRetenido' => 0,
                    'descripcion' => 'Descripción.',
                ],
            ],
            'resumen' => [
                'totalSujetoRetencion' => 0,
                'totalIVAretenido' => 0,
                'totalIVAretenidoLetras' => $this->numberToLetter->convert(0.00),
            ],
            'extension' => null,
            'apendice' => null,
        ];
    }
}
