<?php

namespace App\Models\Management;

use App\Models\Configuration\MenuModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Permission;
use App\Traits\DataViewer;

class PermissionModel extends Permission
{
    use DataViewer;

    protected $connection = 'pgsql';
    protected $table = 'permissions';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'guard_name', 'menu_id'];
    protected $hidden = ['created_at', 'updated_at'];
    protected array $allowedFilters = ['id', 'name', 'menu_id'];
    protected array $orderable = ['id', 'name', 'menu_id'];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(MenuModel::class, 'menu_id', 'id');
    }
}
