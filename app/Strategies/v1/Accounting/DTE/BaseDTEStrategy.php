<?php

namespace App\Strategies\v1\Accounting\DTE;

use App\Contracts\v1\Accounting\DTE\DTEGeneratorInterface;
use App\Enums\v1\Billing\DocumentTypes;
use App\Libraries\Accounting\DTE\HeaderUtils;
use App\Libraries\Accounting\DTE\IssuerUtils;
use App\Libraries\NumberToLetter;
use App\Models\Billing\Options\PaymentMethodModel;

abstract class BaseDTEStrategy implements DTEGeneratorInterface
{
    protected const TASA_VALOR_NETO = 1.13;
    protected const TASA_IVA = 0.13;
    protected const TASA_IVA_RETENIDO = 0.01;

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
     * Construye el bloque "identificacion" del DTE.
     *
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
     * Construye el bloque "emisor" del DTE.
     *
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
     * Aplica el schema sobre el emisor base.
     *
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
     *
     * @param string $number
     * @return string
     */
    protected function parseNumber(string $number): string
    {
        return str_replace('-', '', $number);
    }

    /***
     * Formatea números de telefono a formato aceptado por hacienda (8 dígitos sin separadores).
     * Retorna null si el número no tiene exactamente 8 dígitos.
     *
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
     * Redondea a 2 decimales usando bcmath para evitar perdida de precisión.
     *
     * @param float|int $number
     * @return float
     */
    protected function round2(float|int $number): float
    {
        return bcadd((string)$number, '0', 2);
    }

    /***
     * Retorna el código del método de pago.
     *
     * @param int $methodId
     * @return string
     */
    protected function paymentMethodCode(int $methodId): string
    {
        $query = PaymentMethodModel::query()->findOrFail($methodId);
        return $query->code;
    }
}
