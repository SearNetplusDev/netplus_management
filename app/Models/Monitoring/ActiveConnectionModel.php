<?php

namespace App\Models\Monitoring;

use App\Models\Services\ServiceInternetModel;
use App\Traits\DataViewer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read ServiceInternetModel|null $internet_service
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActiveConnectionModel advancedFilter()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActiveConnectionModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActiveConnectionModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActiveConnectionModel onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActiveConnectionModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActiveConnectionModel withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActiveConnectionModel withoutTrashed()
 * @mixin \Eloquent
 */
class ActiveConnectionModel extends Model
{
    use SoftDeletes, DataViewer;

    protected $connection = 'pgsql';
    protected $table = 'internet_active_connections';
    protected $fillable = [
        'internet_service_id',
        'pppoe_user',
        'ip_address',
        'caller_id',
        'uptime',
        'uptime_seconds',
        'mikrotik_ref_id',
        'last_synced_at',
    ];
    protected $primaryKey = 'id';
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = [
        'id',
        'pppoe_user',
        'ip_address',
        'caller_id',
        'uptime',
        'uptime_seconds',
        'mikrotik_ref_id',
    ];
    protected array $orderable = [
        'id',
        'pppoe_user',
        'ip_address',
        'caller_id',
        'uptime',
        'uptime_seconds',
        'mikrotik_ref_id',
    ];
    protected $casts = ['last_synced_at' => 'datetime'];

    public function internet_service(): BelongsTo
    {
        return $this->belongsTo(ServiceInternetModel::class, 'internet_service_id', 'id');
    }
}
