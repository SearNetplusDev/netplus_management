<?php

namespace App\Models\Billing;

use App\Enums\v1\General\BillingStatus;
use App\Models\Billing\Options\StatusModel;
use App\Models\Clients\ClientModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property BillingStatus $status_id
 * @property-read ClientModel|null $client
 * @property-read StatusModel|null $status
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientFinancialStatusModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientFinancialStatusModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientFinancialStatusModel onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientFinancialStatusModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientFinancialStatusModel withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ClientFinancialStatusModel withoutTrashed()
 * @mixin \Eloquent
 */
class ClientFinancialStatusModel extends Model
{
    use SoftDeletes;

    protected $connection = 'pgsql';
    protected $table = 'clients_financial_status';
    protected $primaryKey = 'id';
    protected $fillable = [
        'client_id',            //  Id de Cliente
        'current_balance',      //  Saldo de facturas que no sean pagadas
        'overdue_balance',      //  Saldo vencido
        'total_paid_amount',    //  Cantidad pagada
        'total_invoices',       //  Facturas totales generadas
        'pending_invoices',     //  Facturas pendientes
        'overdue_invoices',     //  Facturas vencidas
        'status_id',            //  Estado financiero
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts = [
        'client_id' => 'integer',
        'current_balance' => 'decimal:8',
        'overdue_balance' => 'decimal:8',
        'total_paid_amount' => 'decimal:8',
        'total_invoices' => 'integer',
        'pending_invoices' => 'integer',
        'overdue_invoices' => 'integer',
        'status_id' => BillingStatus::class,
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientModel::class, 'client_id', 'id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(StatusModel::class, 'status_id', 'id');
    }
}
