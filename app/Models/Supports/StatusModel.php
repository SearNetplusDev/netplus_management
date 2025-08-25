<?php

namespace App\Models\Supports;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasStatusTrait;
use App\Traits\DataViewer;

class StatusModel extends Model
{
    use SoftDeletes, DataViewer, HasStatusTrait;

    protected $connection = 'pgsql';
    protected $table = 'supports_status';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'badge_color', 'status_id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = ['id', 'name', 'status_id'];
    protected array $orderable = ['id', 'name', 'status_id'];
    protected $appends = ['status'];
}
