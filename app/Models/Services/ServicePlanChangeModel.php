<?php

namespace App\Models\Services;

use App\Models\Management\Profiles\InternetModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServicePlanChangeModel extends Model
{
    use SoftDeletes;

    protected $connection = 'pgsql';
    protected $table = 'services_plan_changes';
    protected $primaryKey = 'id';
    protected $fillable = [
        'service_id',
        'old_internet_profile_id',
        'new_internet_profile_id',
        'change_date',
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts = [
        'change_date' => 'date',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceModel::class, 'service_id', 'id');
    }

    public function old_internet_profile(): BelongsTo
    {
        return $this->belongsTo(InternetModel::class, 'old_internet_profile_id', 'id');
    }

    public function new_internet_profile(): BelongsTo
    {
        return $this->belongsTo(InternetModel::class, 'new_internet_profile_id', 'id');
    }
}
