<?php

namespace App\Models\Billing\Options;

use App\Traits\DataViewer;
use App\Traits\HasStatusTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethodModel extends Model
{
    use DataViewer, HasStatusTrait, SoftDeletes;

    protected $connection = 'pgsql';
    protected $table = 'billing_payment_methods';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'code',
        'badge_color',
        'status_id',
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = [
        'id',
        'name',
        'code',
        'status_id',
    ];
    protected array $orderable = [
        'id',
        'name',
        'code',
        'status_id',
    ];
    protected $appends = ['status'];
}
