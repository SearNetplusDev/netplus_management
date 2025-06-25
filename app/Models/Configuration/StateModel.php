<?php

namespace App\Models\Configuration;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DataViewer;

class StateModel extends Model
{
    use DataViewer, SoftDeletes;

    protected $connection = 'pgsql';
    protected $table = 'config_states';
    protected $fillable = ['name', 'code', 'iso_code', 'status_id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = ['id', 'name', 'code', 'iso_code', 'status_id'];
    protected array $orderable = ['id', 'name', 'code', 'status_id'];
}
