<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceDiscountModel extends Pivot
{
    use SoftDeletes;

    protected $connection = 'pgsql';
    protected $table = 'billing_invoice_discounts';
    protected $fillable = [
        'invoice_id',
        'discount_id',
        'applied_amount',
    ];
    protected $casts = [
        'applied_amount' => 'decimal:8',
    ];
}
