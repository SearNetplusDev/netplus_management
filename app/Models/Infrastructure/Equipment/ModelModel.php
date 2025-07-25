<?php

namespace App\Models\Infrastructure\Equipment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DataViewer;
use App\Traits\HasStatusTrait;

class ModelModel extends Model
{
    use SoftDeletes, DataViewer, HasStatusTrait;

    protected $connection = 'pgsql';
    protected $table = 'infrastructure_equipment_models';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'equipment_type_id', 'brand_id', 'status_id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = ['id', 'name', 'equipment_type_id', 'brand_id', 'status_id'];
    protected array $orderable = ['id', 'name', 'equipment_type_id', 'brand_id', 'status_id'];
    protected $appends = ['status'];

    public function brand(): HasOne
    {
        return $this->hasOne(BrandModel::class, 'id', 'brand_id')
            ->where('status_id', 1);
    }

    public function type(): HasOne
    {
        return $this->hasOne(TypeModel::class, 'id', 'equipment_type_id')
            ->where('status_id', 1);
    }
}
