<?php

namespace App\Models\Configuration;

use App\Models\Management\PermissionModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DataViewer;
use App\Traits\HasStatusTrait;

class MenuModel extends Model
{
    use SoftDeletes, DataViewer, HasStatusTrait;

    protected $connection = 'pgsql';
    protected $table = 'config_menu';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'url', 'slug', 'icon', 'parent_id', 'order', 'status_id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = ['id', 'name', 'url', 'status_id'];
    protected array $orderable = ['id', 'name', 'status_id'];
    protected $appends = ['status'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuModel::class, 'parent_id')
            ->where('status_id', 1);
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuModel::class, 'parent_id')
            ->where('status_id', 1)
            ->orderBy('order')
            ->with('children');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(PermissionModel::class, 'menu_id', 'id');
    }
}
