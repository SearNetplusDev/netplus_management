<?php

namespace App\Models\Billing\Options;

use App\Traits\DataViewer;
use App\Traits\HasStatusTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StatusModel extends Model
{
    use SoftDeletes, HasStatusTrait, DataViewer;

    protected $connection = 'pgsql';
    protected $table = 'billing_statuses';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'badge_color', 'status_id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = ['id', 'name', 'status_id'];
    protected array $orderable = ['id', 'name', 'status_id'];
    protected $appends = ['status'];
}
