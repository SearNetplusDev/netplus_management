<?php

namespace App\Models\Accounting;

use App\Models\Accounting\Config\EventTypeModel;
use App\Traits\DataViewer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read \App\Models\Accounting\DTEModel|null $dte
 * @property-read EventTypeModel|null $event
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DTEEventModel advancedFilter()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DTEEventModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DTEEventModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DTEEventModel onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DTEEventModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DTEEventModel withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DTEEventModel withoutTrashed()
 * @mixin \Eloquent
 */
class DTEEventModel extends Model
{
    use SoftDeletes, DataViewer;

    protected $connection = 'pgsql';
    protected $table = 'accounting_dte_events';
    protected $primaryKey = 'id';
    protected $fillable = [
        'dte_id',
        'generation_code',
        'reception_stamp',
        'generation_datetime',
        'user_id',
        'json_body',
        'status_id',
        'event_type_id',
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = [
        'id',
        'dte_id',
        'generation_code',
        'generation_datetime',
        'user_id',
        'status_id',
        'event_type_id',
    ];
    protected array $orderable = [
        'id',
        'generation_datetime',
        'status_id',
    ];
    protected $casts = [
        'generation_datetime' => 'datetime',
        'json_body' => 'array',
        'status_id' => 'boolean',
    ];

    public function dte(): BelongsTo
    {
        return $this->belongsTo(DTEModel::class, 'dte_id', 'id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(EventTypeModel::class, 'event_type_id', 'id');
    }
}
