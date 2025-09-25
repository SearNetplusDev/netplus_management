<?php

namespace App\Models\Clients;

use App\Models\Configuration\Clients\ContractStateModel;
use App\Models\Supports\SupportModel;
use App\Observers\Clients\ContractObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasStatusTrait;

#[ObservedBy(ContractObserver::class)]
class ContractModel extends Model
{
    use HasStatusTrait, SoftDeletes;

    protected $connection = 'pgsql';
    protected $table = 'clients_contracts';
    protected $primaryKey = 'id';
    protected $fillable = [
        'client_id',
        'contract_date',
        'contract_end_date',
        'installation_price',
        'contract_amount',
        'contract_status_id',
        'status_id'
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected $appends = ['status', 'diff_days'];

    public function contract_status(): HasOne
    {
        return $this->hasOne(ContractStateModel::class, 'id', 'contract_status_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientModel::class, 'client_id', 'id');
    }

    public function support(): HasOne
    {
        return $this->hasOne(SupportModel::class, 'contract_id', 'id');
    }

    public function getDiffDaysAttribute(): ?int
    {
        if (!$this->contract_end_date) return null;
        try {
            $today = Carbon::today();
            $end = Carbon::parse($this->contract_end_date);
            return $today->diffInDays($end, false);
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }
}
