<?php

namespace App\Models\Billing;

use App\Models\Billing\Options\StatusModel;
use App\Models\Clients\ClientModel;
use App\Traits\HasStatusTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceModel extends Model
{
    use HasStatusTrait, SoftDeletes;

    protected $connection = 'pgsql';
    protected $table = 'billing_invoices';
    protected $primaryKey = 'id';
    protected $fillable = [
        'client_id',            //  Cliente
        'billing_period_id',    //  Período
        'invoice_type',         //  Tipo de factura (individual, consolidada)
        'subtotal',             //  Subtotal
        'iva',                  //  IVA
        'iva_retenido',         //  IVA retenido
        'total_amount',         //  Total
        'paid_amount',          //  Cantidad pagada
        'balance_due',          //  Saldo pendiente
        'billing_status_id',    //  Estado financiero
        'status_id',            //  Estado (activo, inactivo)
        'comments'              //  Observaciones
    ];
    protected $casts = [
        'client_id' => 'integer',
        'billing_period_id' => 'integer',
        'invoice_type' => 'integer',
        'billing_status_id' => 'integer',

        'subtotal' => 'decimal:8',
        'iva' => 'decimal:8',
        'iva_retenido' => 'decimal:8',
        'total_amount' => 'decimal:8',
        'paid_amount' => 'decimal:8',
        'balance_due' => 'decimal:8',

        'status_id' => 'boolean',
    ];
    protected $appends = ['status'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientModel::class, 'client_id', 'id');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(PeriodModel::class, 'billing_period_id', 'id');
    }

    public function items(): BelongsTo
    {
        return $this->belongsTo(InvoiceDetailModel::class, 'invoice_id', 'id');
    }

    public function financial_status(): BelongsTo
    {
        return $this->belongsTo(StatusModel::class, 'status_id', 'id');
    }

    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(
            DiscountModel::class,
            'billing_invoice_discounts',
            'invoice_id',
            'discount_id',
        )
            ->using(InvoiceDiscountModel::class)
            ->withPivot(['applied_amount'])
            ->withTimestamps()
            ->withTrashed();
    }
}
