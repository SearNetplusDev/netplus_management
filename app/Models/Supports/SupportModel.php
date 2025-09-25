<?php

namespace App\Models\Supports;

use App\Models\Clients\ClientModel;
use App\Models\Clients\ContractModel;
use App\Models\Configuration\BranchModel;
use App\Models\Configuration\Geography\DistrictModel;
use App\Models\Configuration\Geography\MunicipalityModel;
use App\Models\Configuration\Geography\StateModel;
use App\Models\Management\TechnicianModel;
use App\Models\Services\ServiceModel;
use App\Models\User;
use App\Observers\Supports\SupportObserver;
use App\Traits\DataViewer;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([SupportObserver::class])]
class SupportModel extends Model
{
    use SoftDeletes, DataViewer;

    protected $connection = 'pgsql';
    protected $table = 'supports';
    protected $primaryKey = 'id';
    protected $fillable = [
        'type_id',
        'ticket_number',
        'client_id',
        'service_id',
        'contract_id',
        'branch_id',
        'creation_date',
        'due_date',
        'description',
        'technician_id',
        'state_id',
        'municipality_id',
        'district_id',
        'address',
        'closed_at',
        'solution',
        'comments',
        'user_id',
        'status_id',
        'breached_sla',
        'resolution_time',
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = [
        'id',
        'type_id',
        'ticket_number',
        'client_id',
        'service_id',
        'branch_id',
        'creation_date',
        'due_date',
        'technician_id',
        'state_id',
        'municipality_id',
        'district_id',
        'closed_at',
        'user_id',
        'status_id',
        'breached_sla',
        'client.name',
        'client.surname',
        'client.dui.number',
    ];
    protected array $orderable = [
        'id',
        'type_id',
        'ticket_number',
        'client_id',
        'service_id',
        'branch_id',
        'creation_date',
        'due_date',
        'technician_id',
        'state_id',
        'municipality_id',
        'district_id',
        'closed_at',
        'user_id',
        'status_id',
        'breached_sla',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(TypeModel::class, 'type_id', 'id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientModel::class, 'client_id', 'id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceModel::class, 'service_id', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(BranchModel::class, 'branch_id', 'id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(TechnicianModel::class, 'technician_id', 'id');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(StateModel::class, 'state_id', 'id');
    }

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(MunicipalityModel::class, 'municipality_id', 'id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(DistrictModel::class, 'district_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(StatusModel::class, 'status_id', 'id');
    }

    public function details(): HasOne
    {
        return $this->hasOne(SupportDetailModel::class, 'support_id', 'id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(ContractModel::class, 'contract_id', 'id');
    }
}
