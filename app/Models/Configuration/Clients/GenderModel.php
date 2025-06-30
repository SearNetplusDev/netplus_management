<?php

namespace App\Models\Configuration\Clients;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DataViewer;
use App\Traits\HasStatusTrait;

class GenderModel extends Model
{
    use SoftDeletes, HasStatusTrait, DataViewer;

    protected $connection = 'pgsql';
    protected $table = 'config_genders';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'status_id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = ['id', 'name', 'status_id'];
    protected array $orderable = ['id', 'name', 'status_id'];
    protected $appends = ['status'];

}
