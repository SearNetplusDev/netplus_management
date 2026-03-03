<?php

namespace App\Strategies\v1\Accounting\DTE;

use App\Contracts\v1\Accounting\DTE\DTEGeneratorInterface;
use App\Libraries\Accounting\DTE\HeaderUtils;
use App\Libraries\Accounting\DTE\IssuerUtils;
use Carbon\Carbon;

class AnulacionStrategy implements DTEGeneratorInterface
{
    /***
     * @param HeaderUtils $headerUtils
     * @param IssuerUtils $issuerUtils
     */
    public function __construct(
        private HeaderUtils $headerUtils,
        private IssuerUtils $issuerUtils
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
        $genCode = $this->headerUtils->generationCode();
        return [
            'identificacion' => [
                'version' => 2,
                'ambiente' => '01',
                'codigoGeneracion' => $genCode,
                'fecAnula' => Carbon::today()->toDateString(),
                'horAnula' => Carbon::now()->toTimeString(),
            ],
            'emisor' => [
                'nit' => $this->issuerUtils->getNit(),
                'nombre' => $this->issuerUtils->getName(),
                'tipoEstablecimiento' => '02',
                'nomEstablecimiento' => 'Casa Matriz',
                'codEstableMH' => null,
                'codEstable' => null,
                'codPuntoVentaMH' => null,
                'codPuntoVenta' => null,
                'telefono' => $this->issuerUtils->getPhoneNumber(),
                'correo' => $this->issuerUtils->getEmail(),
            ],
            'documento' => [
                'tipoDte' => '',
                'codigoGeneracion' => '',
                'selloRecibido' => '',
                'numeroControl' => '',
                'fecEmi' => '',
                'montoIva' => 0,
                'codigoGeneracionR' => $genCode,
                'tipoDocumento' => '',  // 02. Carnet Residencia, 03. Pasaporte, 13. DUI, 36. NIT, 37. Otro
                'numDocumento' => '',
                'nombre' => 'John Doe',
                'telefono' => '70000000',
                'correo' => 'johndoe@gmail.com',
            ],
            'motivo' => [
                'tipoAnulacion' => 2, // 1. Error en los datos del DTE, 2. Rescindir de la operación, 3. Otro
                'motivoAnulacion' => '',
                'nombreResponsable' => '',
                'tipDocResponsable' => '',  // 02. Carnet Residencia, 03. Pasaporte, 13. DUI, 36. NIT, 37. Otro
                'numDocResponsable' => '',
                'nombreSolicita' => '',
                'tipDocSolicita' => '',
                'numDocSolicita' => ''
            ],
        ];
    }
}
