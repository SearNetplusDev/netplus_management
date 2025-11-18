<?php

namespace App\Models\Billing;

use App\Models\Billing\Options\PaymentMethodModel;
use App\Models\Clients\ClientModel;
use App\Models\User;
use App\Traits\HasStatusTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentModel extends Model
{
    use SoftDeletes, HasStatusTrait;

    protected $connection = 'pgsql';
    protected $table = 'billing_payments';
    protected $primaryKey = 'id';
    protected $fillable = [
        'invoice_id',
        'client_id',
        'payment_method_id',
        'amount',
        'payment_date',
        'reference_number',
        'user_id',
        'comments',
        'status_id',
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];
    protected $appends = ['status'];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(InvoiceModel::class, 'invoice_id', 'id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientModel::class, 'client_id', 'id');
    }

    public function payment_method(): BelongsTo
    {
        return $this->belongsTo(PaymentMethodModel::class, 'payment_method_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
