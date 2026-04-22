<?php

namespace App\Models\Accounting;

use App\Models\Billing\InvoiceModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class DTEInvoiceModel extends Pivot
{
    use SoftDeletes;

    protected $connection = 'pgsql';
    protected $table = 'accounting_dte_invoices';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $fillable = ['dte_id', 'invoice_id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function dte(): BelongsTo
    {
        return $this->belongsTo(DTEModel::class, 'dte_id', 'id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(InvoiceModel::class, 'invoice_id', 'id');
    }
}
