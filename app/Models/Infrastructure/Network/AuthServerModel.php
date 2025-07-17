<?php

namespace App\Models\Infrastructure\Network;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DataViewer;
use App\Traits\HasStatusTrait;

class AuthServerModel extends Model
{
    use DataViewer, HasStatusTrait, SoftDeletes;

    protected $connection = 'pgsql';
    protected $table = 'infrastructure_auth_servers';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'user', 'secret', 'ip', 'port', 'status_id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = ['id', 'name', 'ip', 'status_id'];
    protected array $orderable = ['id', 'name', 'ip', 'status_id'];
    protected $appends = ['status'];
}
