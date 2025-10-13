<?php

namespace App\Models\Services;

use App\Models\Infrastructure\Equipment\InventoryModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceSoldDeviceModel extends Model
{
    use SoftDeletes;

    protected $connection = 'pgsql';
    protected $table = 'services_sold_devices';
    protected $primaryKey = 'id';
    protected $fillable = ['equipment_id', 'service_id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(InventoryModel::class, 'equipment_id', 'id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceModel::class, 'service_id', 'id');
    }
}
