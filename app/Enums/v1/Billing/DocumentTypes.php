<?php

namespace App\Enums\v1\Billing;

use function Laravel\Prompts\select;

enum DocumentTypes: int
{
    case FACTURA = 1;
    case CREDITO_FISCAL = 2;
    case NOTA_REMISION = 3;
    case NOTA_CREDITO = 4;
    case NOTA_DEBITO = 5;
    case COMPROBANTE_RETENCION = 6;
    case COMPROBANTE_LIQUIDACION = 7;
    case DOCUMENTO_CONTABLE_LIQUIDACION = 8;
    case FACTURA_EXPORTACION = 9;
    case FACTURA_SUJETO_EXCLUIDO = 10;
    case COMPROBANTE_DONACION = 11;

    public function label(): string
    {
        return match ($this) {
            self::FACTURA => 'Factura',
            self::CREDITO_FISCAL => 'Crédito fiscal',
            self::NOTA_REMISION => 'Nota de remisión',
            self::NOTA_CREDITO => 'Nota de crédito',
            self::NOTA_DEBITO => 'Nota de debito',
            self::COMPROBANTE_RETENCION => 'Comprobante de retención',
            self::COMPROBANTE_LIQUIDACION => 'Comprobante de liquidación',
            self::DOCUMENTO_CONTABLE_LIQUIDACION => 'Documento contable de liquidación',
            self::FACTURA_EXPORTACION => 'Factura de exportación',
            self::FACTURA_SUJETO_EXCLUIDO => 'Factura de sujeto excluído',
            self::COMPROBANTE_DONACION => 'Comprobante de donación',
            default => 'No identificado',
        };
    }

    public function code(): string
    {
        return match ($this) {
            self::FACTURA => '01',
            self::CREDITO_FISCAL => '03',
            self::NOTA_REMISION => '04',
            self::NOTA_CREDITO => '05',
            self::NOTA_DEBITO => '06',
            self::COMPROBANTE_RETENCION => '07',
            self::COMPROBANTE_LIQUIDACION => '08',
            self::DOCUMENTO_CONTABLE_LIQUIDACION => '09',
            self::FACTURA_EXPORTACION => '11',
            self::FACTURA_SUJETO_EXCLUIDO => '14',
            self::COMPROBANTE_DONACION => '15',
            default => '00',
        };
    }
}
