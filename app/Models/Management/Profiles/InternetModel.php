<?php

namespace App\Models\Management\Profiles;

use App\Models\Services\ServiceInternetModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DataViewer;
use App\Traits\HasStatusTrait;

class InternetModel extends Model
{
    use SoftDeletes, DataViewer, HasStatusTrait;

    protected $connection = 'pgsql';
    protected $table = 'management_internet_profiles';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'alias',
        'description',
        'mk_profile',
        'debt_profile',
        'net_value',
        'iva',
        'price',
        'expiration_date',
        'status_id'
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = [
        'id',
        'name',
        'alias',
        'mk_profile',
        'price',
        'status_id',
    ];
    protected array $orderable = [
        'id',
        'name',
        'alias',
        'price',
        'status_id',
    ];
    protected $appends = ['status'];

    public function service_internet(): HasMany
    {
        return $this->hasMany(ServiceInternetModel::class, 'service_id', 'id');
    }
}
