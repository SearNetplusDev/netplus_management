<?php

namespace App\Models\Services\Logs;

use App\Models\Infrastructure\Equipment\InventoryModel;
use App\Models\Services\ServiceSoldDeviceModel;
use App\Models\Services\ServiceModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceSoldDeviceLog extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'services_sold_devices_logs';
    protected $primaryKey = 'id';
    protected $fillable = [
        'service_sold_device_id',
        'service_id',
        'equipment_id',
        'user_id',
        'action',
        'before',
        'after',
    ];
    protected $hidden = ['updated_at'];
    protected $casts = [
        'before' => 'array',
        'after' => 'array',
    ];

    public function sold_device(): BelongsTo
    {
        return $this->belongsTo(ServiceSoldDeviceModel::class, 'service_sold_device_id', 'id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceModel::class, 'service_id', 'id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(InventoryModel::class, 'equipment_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
