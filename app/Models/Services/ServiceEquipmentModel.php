<?php

namespace App\Models\Services;

use App\Models\Infrastructure\Equipment\InventoryModel;
use App\Observers\Services\ServiceEquipmentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DataViewer;
use App\Traits\HasStatusTrait;

#[ObservedBy(ServiceEquipmentObserver::class)]
class ServiceEquipmentModel extends Model
{
    use SoftDeletes, DataViewer, HasStatusTrait;

    protected $connection = 'pgsql';
    protected $table = 'services_equipment';
    protected $primaryKey = 'id';
    protected $fillable = ['equipment_id', 'service_id', 'status_id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = ['id', 'equipment_id', 'service_id', 'status_id'];
    protected array $orderable = ['id', 'equipment_id', 'service_id', 'status_id'];
    protected $appends = ['status'];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(InventoryModel::class, 'equipment_id', 'id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceModel::class, 'service_id', 'id');
    }
}
