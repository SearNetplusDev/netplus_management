<?php

namespace App\Models\Infrastructure\Equipment;

use App\Models\Configuration\BranchModel;
use App\Models\Configuration\Infrastructure\EquipmentStatusModel;
use App\Models\Management\TechnicianModel;
use App\Models\Services\ServiceModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DataViewer;

class InventoryModel extends Model
{
    use SoftDeletes, DataViewer;

    protected $connection = 'pgsql';
    protected $table = 'infrastructure_residential_equipment_inventory';
    protected $primaryKey = 'id';
    protected $fillable = [
        'brand_id',
        'type_id',
        'model_id',
        'service_id',
        'branch_id',
        'mac_address',
        'serial_number',
        'registration_date',
        'installation_date',
        'user_id',
        'technician_id',
        'status_id',
        'comments'
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = [
        'id',
        'type_id',
        'model_id',
        'service_id',
        'branch_id',
        'mac_address',
        'serial_number',
        'technician_id',
        'status_id',
    ];
    protected array $orderable = [
        'id',
        'type_id',
        'model_id',
        'service_id',
        'branch_id',
        'mac_address',
        'serial_number',
        'technician_id',
        'status_id',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(BrandModel::class, 'brand_id', 'id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(TypeModel::class, 'type_id', 'id');
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(ModelModel::class, 'model_id', 'id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceModel::class, 'service_id', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(BranchModel::class, 'branch_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(TechnicianModel::class, 'technician_id', 'id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(EquipmentStatusModel::class, 'status_id', 'id');
    }
}
