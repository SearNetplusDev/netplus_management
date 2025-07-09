<?php

namespace App\Models\Clients;

use App\Models\Configuration\Geography\DistrictModel;
use App\Models\Configuration\Geography\MunicipalityModel;
use App\Models\Configuration\Geography\StateModel;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DataViewer;
use App\Traits\HasStatusTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class AddressModel extends Model
{
    use DataViewer, HasStatusTrait, SoftDeletes;

    protected $connection = 'pgsql';
    protected $table = 'clients_addresses';
    protected $primaryKey = 'id';
    protected $fillable = [
        'client_id',
        'neighborhood',
        'address',
        'state_id',
        'municipality_id',
        'district_id',
        'country_id'
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = [
        'id',
        'client_id',
        'neighborhood',
        'state_id',
        'municipality_id',
        'district_id',
        'country_id'
    ];
    protected array $orderable = [
        'id',
        'client_id',
        'neighborhood',
        'state_id',
        'municipality_id',
        'district_id',
        'country_id'
    ];
    protected $appends = ['status'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientModel::class, 'client_id', 'id');
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
}
