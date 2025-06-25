<?php

namespace App\Models\Configuration;

use Illuminate\Database\Eloquent\Model;
use App\Traits\DataViewer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MunicipalityModel extends Model
{
    use SoftDeletes, DataViewer;

    protected $connection = 'pgsql';
    protected $table = 'config_municipalities';
    protected $fillable = ['name', 'code', 'state_id', 'status_id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFields = ['id', 'name', 'code', 'state_id', 'status_id'];
    protected array $orderable = ['id', 'name', 'code', 'state_id', 'status_id'];

    public function State(): BelongsTo
    {
        return $this->belongsTo(StateModel::class, 'state_id', 'id');
    }
}
