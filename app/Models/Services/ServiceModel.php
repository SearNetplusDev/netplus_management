<?php

namespace App\Models\Services;

use App\Models\Clients\ClientModel;
use App\Models\Configuration\Geography\DistrictModel;
use App\Models\Configuration\Geography\MunicipalityModel;
use App\Models\Configuration\Geography\StateModel;
use App\Models\Infrastructure\Network\EquipmentModel;
use App\Models\Infrastructure\Network\NodeModel;
use App\Models\Management\TechnicianModel;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DataViewer;
use App\Traits\HasStatusTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceModel extends Model
{
    use SoftDeletes, DataViewer, HasStatusTrait;

    protected $connection = 'pgsql';
    protected $table = 'services';
    protected $primaryKey = 'id';
    protected $fillable = [
        'client_id',
        'code',
        'name',
        'node_id',
        'equipment_id',
        'installation_date',
        'technician_id',
        'latitude',
        'longitude',
        'state_id',
        'municipality_id',
        'district_id',
        'address',
        'separate_billing',
        'status_id',
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = [
        'id',
        'code',
        'node_id',
        'equipment_id',
        'technician_id',
        'state_id',
        'municipality_id',
        'district_id',
        'status_id',
        'client.name',
        'client.dui.number'
    ];
    protected array $orderable = [
        'id',
        'code',
        'node_id',
        'equipment_id',
        'technician_id',
        'state_id',
        'municipality_id',
        'district_id',
        'status_id',
    ];
    protected $appends = ['status'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientModel::class, 'client_id', 'id');
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(NodeModel::class, 'node_id', 'id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(EquipmentModel::class, 'equipment_id', 'id');
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
}
