<?php

namespace App\Models\Infrastructure\Equipment;

use App\Models\Management\TechnicianModel;
use App\Models\Services\ServiceModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DataViewer;

class InventoryLogModel extends Model
{
    use SoftDeletes, DataViewer;

    protected $connection = 'pgsql';
    protected $table = 'infrastructure_inventory_log';
    protected $primaryKey = 'id';
    protected $fillable = [
        'equipment_id',
        'user_id',
        'technician_id',
        'execution_date',
        'service_id',
        'description'
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = [
        'id',
        'equipment_id',
        'user_id',
        'technician_id',
        'service_id',
    ];
    protected array $orderable = [
        'id',
        'equipment_id',
        'user_id',
        'technician_id',
        'service_id',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(InventoryModel::class, 'equipment_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(TechnicianModel::class, 'technician_id', 'id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceModel::class, 'service_id', 'id');
    }
}
