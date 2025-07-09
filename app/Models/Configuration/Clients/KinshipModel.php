<?php

namespace App\Models\Configuration\Clients;

use Illuminate\Database\Eloquent\Model;
use App\Traits\DataViewer;
use App\Traits\HasStatusTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class KinshipModel extends Model
{
    use SoftDeletes, DataViewer, HasStatusTrait;

    protected $connection = 'pgsql';
    protected $table = 'config_kinships';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'status_id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = ['id', 'name', 'status_id'];
    protected array $orderable = ['id', 'name', 'status_id'];
    protected $appends = ['status'];
}
