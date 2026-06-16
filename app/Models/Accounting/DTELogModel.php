<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $dte_id
 * @property int|null $event_id
 * @property string $json_response
 * @property \Illuminate\Support\Carbon $transaction_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Accounting\DTEModel|null $dte
 * @property-read \App\Models\Accounting\DTEEventModel|null $event
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DTELogModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DTELogModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DTELogModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DTELogModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DTELogModel whereDteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DTELogModel whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DTELogModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DTELogModel whereJsonResponse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DTELogModel whereTransactionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DTELogModel whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DTELogModel extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'accounting_dte_logs';
    protected $primaryKey = 'id';
    protected $fillable = ['dte_id', 'event_id', 'json_response', 'transaction_date'];
    protected $hidden = ['created_at', 'updated_at'];
    protected $casts = [
        'dte_id' => 'integer',
        'event_id' => 'integer',
        'json_response' => 'array',
        'transaction_date' => 'datetime',
    ];

    public function dte(): BelongsTo
    {
        return $this->belongsTo(DTEModel::class, 'dte_id', 'id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(DTEEventModel::class, 'event_id', 'id');
    }
}
