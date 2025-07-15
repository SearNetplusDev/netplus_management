<?php

namespace App\Models\Clients;

use App\Models\Configuration\Clients\ContractStateModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasStatusTrait;

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
    protected $appends = ['status'];

    public function contract_status(): HasOne
    {
        return $this->hasOne(ContractStateModel::class, 'id', 'contract_status_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientModel::class, 'client_id', 'id');
    }
}
