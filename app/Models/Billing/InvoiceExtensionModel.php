<?php

namespace App\Models\Billing;

use App\Models\User;
use App\Traits\DataViewer;
use App\Traits\HasStatusTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceExtensionModel extends Model
{
    use SoftDeletes, HasStatusTrait, DataViewer;

    protected $connection = 'pgsql';
    protected $table = 'billing_invoice_extensions';
    protected $primaryKey = 'id';
    protected $fillable = [
        'invoice_id',           //  Id de factura
        'previous_due_date',    //  Fecha de corte anterior
        'extended_due_date',    //  Fecha de vencimiento de prórroga
        'reason',               //  Motivo
        'user_id',              //  Usuario que crea la prórroga
        'status_id',            //  Estado de la prórroga
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = [
        'id',
        'invoice_id',
        'previous_due_date',
        'extended_due_date',
        'user_id',
        'status_id',
    ];
    protected array $orderable = [
        'id',
        'invoice_id',
        'previous_due_date',
        'extended_due_date',
        'user_id',
        'status_id',
    ];
    protected $casts = [
        'previous_due_date' => 'date',
        'extended_due_date' => 'date',
    ];
    protected $appends = ['status'];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(InvoiceModel::class, 'invoice_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
