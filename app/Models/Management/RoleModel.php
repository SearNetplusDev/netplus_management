<?php

namespace App\Models\Management;

use App\Traits\DataViewer;
use Spatie\Permission\Models\Role;


class RoleModel extends Role
{
    use DataViewer;

    protected $connection = 'pgsql';
    protected $table = 'roles';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'guard_name', 'homepage'];
    protected $hidden = ['created_at', 'updated_at'];
    protected array $allowedFilters = ['id', 'name'];
    protected array $orderable = ['id', 'name'];
}
