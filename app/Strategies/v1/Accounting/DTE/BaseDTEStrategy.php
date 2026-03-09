<?php

namespace App\Strategies\v1\Accounting\DTE;

use App\Contracts\v1\Accounting\DTE\DTEGeneratorInterface;
use App\Enums\v1\Billing\DocumentTypes;
use App\Libraries\Accounting\DTE\HeaderUtils;
use App\Libraries\Accounting\DTE\IssuerUtils;
use App\Libraries\NumberToLetter;

abstract class BaseDTEStrategy implements DTEGeneratorInterface
{
    /***
     * @param HeaderUtils $headerUtils
     * @param IssuerUtils $issuerUtils
     * @param NumberToLetter $numberToLetter
     */
    public function __construct(
        protected HeaderUtils    $headerUtils,
        protected IssuerUtils    $issuerUtils,
        protected NumberToLetter $numberToLetter,
    )
    {

    }

    /***
     * @param array $data
     * @return array
     */
    public function generate(array $data): array
    {
        return $this->buildBody($data);
    }

    /***
     * @param array $data
     * @return array
     */
    abstract protected function buildBody(array $data): array;

    /***
     * @return array
     */
    protected function identificacionSchema(): array
    {
        return [];
    }

    /***
     * @return array
     */
    protected function emisorSchema(): array
    {
        return [];
    }


    /***
     * Campo identificación del DTE
     * @param DocumentTypes $type
     * @param int $version
     * @return array
     * @throws \Random\RandomException
     */
    protected function identificacion(DocumentTypes $type, int $version = 1): array
    {
        $base = [
            'version' => $version,
            'ambiente' => $this->headerUtils->ambient(),
            'tipoDte' => $type->code(),
            'numeroControl' => $this->headerUtils->controlNumber($type),
            'codigoGeneracion' => $this->headerUtils->generationCode(),
            'tipoModelo' => 1,
            'tipoOperacion' => 1,
            'fecEmi' => $this->headerUtils->getDate(),
            'horEmi' => $this->headerUtils->getHour(),
            'tipoMoneda' => $this->headerUtils->getCurrency(),
            'tipoContingencia' => null,
            'motivoContin' => null,
        ];
        return $this->applyFieldSchema($base, $this->identificacionSchema());
    }

    /***
     * Elementos del campo emisor del DTE.
     * @return array
     */
    protected function emisorBase(): array
    {
        return [
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
        ];
    }

    /***
     * @return array
     */
    protected function emisor(): array
    {
        return $this->applyFieldSchema($this->emisorBase(), $this->emisorSchema());
    }

    /***
     * Motor de Schemas
     * - true => incluir con el nombre original
     * - false => excluir
     * - 'nuevoNombre' => incluir renombrado
     * - (ausente) => incluir con nombre original
     * @param array $base
     * @param array $schema
     * @return array
     */
    protected function applyFieldSchema(array $base, array $schema): array
    {
        if (empty($schema)) {
            return $base;
        }

        $result = [];

        foreach ($schema as $originalKey => $rule) {
            if ($rule === false) {
                continue;
            }
            if (!array_key_exists($originalKey, $base)) {
                continue;
            }
            $result[is_string($rule) ? $rule : $originalKey] = $base[$originalKey];
        }

        foreach ($base as $key => $value) {
            $inResult = array_key_exists($key, $result);
            $renamedTo = is_string($schema[$key] ?? null) ? $schema[$key] : null;
            $inResult = $inResult || ($renamedTo && array_key_exists($renamedTo, $result));

            if (!$inResult && ($schema[$key] ?? true) !== false) {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /***
     * Remueve guiones de una cadena de texto.
     * @param string $number
     * @return string
     */
    protected function parseNumber(string $number): string
    {
        return str_replace('-', '', $number);
    }

    /***
     * Formatea números de telefono a formato aceptado por hacienda
     * @param string $phone
     * @return string|null
     */
    protected function phoneFormatter(string $phone): ?string
    {
        $clear = str_replace(['-', ' '], '', $phone);

        if (strlen($clear) === 8) {
            return $clear;
        }

        return null;
    }

    /***
     * Redondea a 2 decimales los números
     * @param float|int $number
     * @return float
     */
    protected function round2(float|int $number): float
    {
        return round((float)$number, 2);
    }
}
