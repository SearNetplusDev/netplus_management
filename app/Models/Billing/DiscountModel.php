<?php

namespace App\Models\Billing;

use App\Traits\DataViewer;
use App\Traits\HasStatusTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscountModel extends Model
{
    use SoftDeletes, HasStatusTrait, DataViewer;

    protected $connection = 'pgsql';
    protected $table = 'billing_discounts';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',             //  Nombre
        'code',             //  Código
        'description',      //  Descripción
        'percentage',       //  Porcentaje
        'amount',           //  Monto fijo
        'status_id'         //  Estado
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = [
        'id',
        'name',
        'code',
        'percentage',
        'amount',
        'status_id',
    ];
    protected array $orderable = [
        'id',
        'name',
        'code',
        'percentage',
        'amount',
        'status_id',
    ];
    protected $casts = [
        'percentage' => 'decimal:8',
        'amount' => 'decimal:8'
    ];
    protected $appends = ['status'];

    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(
            InvoiceModel::class,
            'billing_invoice_discounts',
            'discount_id',
            'invoice_id'
        )
            ->using(InvoiceDiscountModel::class)
            ->withPivot(['applied_amount'])
            ->withTimestamps()
            ->withTrashed();
    }
}
