<?php

namespace App\Models\Infrastructure\Network;

use App\Models\Configuration\Geography\DistrictModel;
use App\Models\Configuration\Geography\MunicipalityModel;
use App\Models\Configuration\Geography\StateModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DataViewer;
use App\Traits\HasStatusTrait;

class NodeModel extends Model
{
    use SoftDeletes, DataViewer, HasStatusTrait;

    protected $connection = 'pgsql';
    protected $table = 'infrastructure_nodes';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'server_id',
        'latitude',
        'longitude',
        'state_id',
        'municipality_id',
        'district_id',
        'address',
        'nc',
        'nc_owner',
        'comments',
        'status_id',
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = [
        'id',
        'name',
        'nc',
        'status_id',
    ];
    protected array $orderable = [
        'id',
        'name',
        'status_id',
    ];
    protected $appends = ['status'];

    public function auth_server(): HasOne
    {
        return $this->hasOne(AuthServerModel::class, 'id', 'server_id')
            ->where('status_id', 1);
    }

    public function state(): HasOne
    {
        return $this->hasOne(StateModel::class, 'id', 'state_id');
    }

    public function municipality(): HasOne
    {
        return $this->hasOne(MunicipalityModel::class, 'id', 'municipality_id');
    }

    public function district(): HasOne
    {
        return $this->hasOne(DistrictModel::class, 'id', 'district_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(NodeContactModel::class, 'node_id', 'id')
            ->where('status_id', 1);
    }
}
