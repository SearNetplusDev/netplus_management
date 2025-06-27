<?php

namespace App\Models\Configuration;

use Illuminate\Database\Eloquent\Model;
use App\Traits\DataViewer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistrictModel extends Model
{
    use SoftDeletes, DataViewer;

    protected $connection = 'pgsql';
    protected $table = 'config_districts';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'municipality_id', 'state_id', 'status_id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFields = ['id', 'name', 'municipality_id', 'state_id', 'status_id'];
    protected array $orderable = ['id', 'name', 'municipality_id', 'state_id', 'status_id'];

    public function municipality(): belongsTo
    {
        return $this->belongsTo(MunicipalityModel::class, 'municipality_id', 'id');
    }

    public function state(): belongsTo
    {
        return $this->belongsTo(StateModel::class, 'state_id', 'id');
    }
}
