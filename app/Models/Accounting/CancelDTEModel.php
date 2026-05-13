<?php

namespace App\Models\Accounting;

use App\Traits\DataViewer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $dte_id
 * @property string $generation_code
 * @property string $reception_stamp
 * @property \Illuminate\Support\Carbon $generation_datetime
 * @property int $user_id
 * @property array<array-key, mixed> $json_body
 * @property bool $status_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Accounting\DTEModel|null $dte
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancelDTEModel advancedFilter()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancelDTEModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancelDTEModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancelDTEModel onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancelDTEModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancelDTEModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancelDTEModel whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancelDTEModel whereDteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancelDTEModel whereGenerationCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancelDTEModel whereGenerationDatetime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancelDTEModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancelDTEModel whereJsonBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancelDTEModel whereReceptionStamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancelDTEModel whereStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancelDTEModel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancelDTEModel whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancelDTEModel withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CancelDTEModel withoutTrashed()
 * @mixin \Eloquent
 */
class CancelDTEModel extends Model
{
    use SoftDeletes, DataViewer;

    protected $connection = 'pgsql';
    protected $table = 'accounting_cancelled_dte';
    protected $primaryKey = 'id';
    protected $fillable = [
        'dte_id',
        'generation_code',
        'reception_stamp',
        'generation_datetime',
        'user_id',
        'json_body',
        'status_id',
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = [
        'id',
        'dte_id',
        'generation_code',
        'generation_datetime',
        'user_id',
        'status_id',
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
}
