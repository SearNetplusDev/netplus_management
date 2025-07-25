<?php

namespace App\Models\Infrastructure\Network;

use App\Models\Configuration\Infrastructure\EquipmentStatusModel;
use App\Models\Infrastructure\Equipment\BrandModel;
use App\Models\Infrastructure\Equipment\ModelModel;
use App\Models\Infrastructure\Equipment\TypeModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DataViewer;

class EquipmentModel extends Model
{
    use SoftDeletes, DataViewer;

    protected $connection = 'pgsql';
    protected $table = 'infrastructure_equipment';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'type_id',
        'brand_id',
        'model_id',
        'mac_address',
        'ip_address',
        'username',
        'secret',
        'node_id',
        'comments',
        'status_id',
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = [
        'id',
        'name',
        'type_id',
        'brand_id',
        'model_id',
        'node_id',
        'mac_address',
        'ip_address',
        'status_id',
    ];
    protected array $orderable = [
        'id',
        'name',
        'type_id',
        'brand_id',
        'model_id',
        'node_id',
        'mac_address',
        'ip_address',
        'status_id',
    ];

    public function status(): BelongsTo
    {
        return $this->belongsTo(EquipmentStatusModel::class, 'status_id', 'id')
            ->where('status_id', 1);
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(NodeModel::class, 'node_id', 'id')
            ->where('status_id', 1);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(TypeModel::class, 'type_id', 'id')
            ->where('status_id', 1);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(BrandModel::class, 'brand_id', 'id')
            ->where('status_id', 1);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(ModelModel::class, 'model_id', 'id')
            ->where('status_id', 1);
    }
}
