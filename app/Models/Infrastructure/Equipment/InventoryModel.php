<?php

namespace App\Models\Infrastructure\Equipment;

use App\Models\Configuration\BranchModel;
use App\Models\Configuration\Infrastructure\EquipmentStatusModel;
use App\Models\Services\ServiceEquipmentModel;
use App\Models\Services\ServiceIptvEquipmentModel;
use App\Models\Services\ServiceSoldDeviceModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DataViewer;
use App\Traits\HasCompanyTrait;

class InventoryModel extends Model
{
    use SoftDeletes, DataViewer, HasCompanyTrait;

    protected $connection = 'pgsql';
    protected $table = 'infrastructure_residential_equipment_inventory';
    protected $primaryKey = 'id';
    protected $fillable = [
        'brand_id',
        'type_id',
        'model_id',
        'branch_id',
        'mac_address',
        'serial_number',
        'registration_date',
        'status_id',
        'comments',
        'company_id',
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = [
        'id',
        'type_id',
        'model_id',
        'branch_id',
        'mac_address',
        'serial_number',
        'status_id',
        'company_id',
    ];
    protected array $orderable = [
        'id',
        'type_id',
        'model_id',
        'branch_id',
        'mac_address',
        'serial_number',
        'status_id',
        'company_id',
    ];
    protected $appends = ['company'];

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

    public function branch(): BelongsTo
    {
        return $this->belongsTo(BranchModel::class, 'branch_id', 'id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(EquipmentStatusModel::class, 'status_id', 'id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(InventoryLogModel::class, 'equipment_id', 'id');
    }

    public function last_technician(): HasOne
    {
        return $this->hasOne(InventoryLogModel::class, 'equipment_id', 'id')
            ->latestOfMany('execution_date');
    }

    public function on_internet_service(): HasOne
    {
        return $this->hasOne(ServiceEquipmentModel::class, 'equipment_id', 'id');
    }

    public function on_iptv_service(): HasOne
    {
        return $this->hasOne(ServiceIptvEquipmentModel::class, 'equipment_id', 'id');
    }

    public function on_sold_devices(): HasOne
    {
        return $this->hasOne(ServiceSoldDeviceModel::class, 'equipment_id', 'id');
    }
}
