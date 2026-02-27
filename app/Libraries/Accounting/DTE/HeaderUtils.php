<?php

namespace App\Libraries\Accounting\DTE;

use App\Enums\v1\Billing\DocumentTypes;
use Illuminate\Support\Carbon;

class HeaderUtils
{
    /***
     * Ambiente de producción del DTE
     * @return string
     */
    public function ambient(): string
    {
        return '01';
    }

    /***
     * Genera el número de control según documento a emitir.
     * @param DocumentTypes $type
     * @return string
     */
    public function controlNumber(DocumentTypes $type): string
    {
        return 'DTE-' . $type->code() . '-NTPS2026-123456789012345';
    }

    /***
     * Crea el código de generación del documento.
     * @return string
     * @throws \Random\RandomException
     */
    public function generationCode(): string
    {
        $code = $this->generateHexChar(8) . '-';
        $code .= $this->generateHexChar(4) . '-';
        $code .= $this->generateHexChar(4) . '-';
        $code .= $this->generateHexChar(4) . '-';
        $code .= $this->generateHexChar(12);

        return $code;
    }

    /***
     * Obtiene la fecha en la que se genera el documento.
     * @return string
     */
    public function getDate(): string
    {
        return Carbon::today()->toDateString();
    }

    /***
     * Obtiene la hora exacta en la que se genera el documento.
     * @return string
     */
    public function getHour(): string
    {
        return Carbon::now()->toTimeString();
    }

    /***
     * Tipo de moneda con la cual se emitirá el documento.
     * @return string
     */
    public function getCurrency(): string
    {
        return 'USD';
    }

    public function getNit(): string
    {
        return '';
    }

    /***
     * Genera cadenas hexadecimales de diferente tamaño.
     * @param int $length
     * @return string
     * @throws \Random\RandomException
     */
    private function generateHexChar(int $length): string
    {
        if ($length <= 0) {
            throw new \InvalidArgumentException("La longitud de la cadena debe ser mayor a 0.");
        }

        $bytes = ceil($length / 2);
        $hexString = bin2hex(random_bytes($bytes));

        return strtoupper(substr($hexString, 0, $length));
    }
}
