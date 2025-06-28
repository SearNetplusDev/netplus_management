<?php

namespace App\Models\Configuration;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DataViewer;
use App\Traits\HasStatusTrait;


class BranchModel extends Model
{
    use SoftDeletes, DataViewer, HasStatusTrait;

    protected $connection = 'pgsql';
    protected $table = 'config_branches';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'code',
        'landline',
        'mobile',
        'address',
        'state_id',
        'municipality_id',
        'district_id',
        'country_id',
        'badge_color',
        'status_id'
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = [
        'id',
        'name',
        'code',
        'landline',
        'mobile',
        'state_id',
        'municipality_id',
        'status_id'
    ];
    protected array $orderable = ['id', 'name', 'code', 'status_id'];
    protected $appends = ['status'];

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

    public function country(): HasOne
    {
        return $this->hasOne(CountryModel::class, 'id', 'country_id');
    }
}
