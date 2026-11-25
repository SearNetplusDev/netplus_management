<?php

namespace App\Models\Supports;

use App\Traits\DataViewer;
use App\Traits\HasStatusTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $type_id
 * @property string $name
 * @property string|null $description
 * @property numeric $price
 * @property bool $status_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read array $status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Supports\SupportModel> $supports
 * @property-read int|null $supports_count
 * @property-read \App\Models\Supports\TypeModel|null $type
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceModel advancedFilter()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceModel onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceModel whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceModel whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceModel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceModel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceModel wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceModel whereStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceModel whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceModel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceModel withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceModel withoutTrashed()
 * @mixin \Eloquent
 */
class PriceModel extends Model
{
    use SoftDeletes, DataViewer, HasStatusTrait;

    protected $connection = 'pgsql';
    protected $table = 'supports_prices';
    protected $primaryKey = 'id';
    protected $fillable = [
        'type_id',
        'name',
        'description',
        'price',
        'status_id',
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = [
        'id',
        'type_id',
        'name',
        'price',
        'status_id',
    ];
    protected array $orderable = [
        'id',
        'type_id',
        'name',
        'price',
        'status_id',
    ];
    protected $casts = ['price' => 'decimal:2'];
    protected $appends = ['status'];

    public function type(): BelongsTo
    {
        return $this->belongsTo(TypeModel::class, 'type_id', 'id');
    }

    public function supports(): HasMany
    {
        return $this->hasMany(SupportModel::class, 'price_id', 'id');
    }
}
