<?php

namespace App\Models\Billing;

use App\Models\Services\ServiceModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceDetailModel extends Model
{
    use SoftDeletes;

    protected $connection = 'pgsql';
    protected $table = 'billing_invoice_items';
    protected $primaryKey = 'id';
    protected $fillable = [
        'invoice_id',
        'service_id',
        'description',
        'quantity',
        'unit_price',
        'subtotal',
        'iva',
        'iva_retenido',
        'total',
        'status_id',
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts = [
        'unit_price' => 'decimal:8',
        'subtotal' => 'decimal:8',
        'iva' => 'decimal:8',
        'iva_retenido' => 'decimal:8',
        'total' => 'decimal:8',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(InvoiceModel::class, 'invoice_id', 'id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceModel::class, 'service_id', 'id');
    }
}
