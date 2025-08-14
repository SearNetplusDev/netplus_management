<?php

namespace App\Models\Services;

use App\Models\Management\Profiles\InternetModel;
use App\Models\Services\ServiceModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DataViewer;
use App\Traits\HasStatusTrait;

class ServiceInternetModel extends Model
{
    use SoftDeletes, DataViewer, HasStatusTrait;

    protected $connection = 'pgsql';
    protected $table = 'internet_services';
    protected $primaryKey = 'id';
    protected $fillable = [
        'internet_profile_id',
        'service_id',
        'user',
        'secret',
        'status_id'
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected $allowedFilters = [
        'id',
        'internet_profile_id',
        'service_id',
        'user',
        'secret',
        'status_id'
    ];
    protected $orderable = [
        'id',
        'internet_profile_id',
        'service_id',
        'user',
        'secret',
        'status_id'
    ];
    protected $appends = ['status'];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(InternetModel::class, 'internet_profile_id', 'id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceModel::class, 'service_id', 'id');
    }
}
