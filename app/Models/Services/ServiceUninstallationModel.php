<?php

namespace App\Models\Services;

use App\Models\Management\Profiles\InternetModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceUninstallationModel extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'services_uninstallations';
    protected $primaryKey = 'id';
    protected $fillable = [
        'service_id',
        'internet_profile_id',
        'uninstallation_date',
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts = ['uninstallation_date' => 'date'];

    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceModel::class, 'service_id', 'id');
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(InternetModel::class, 'internet_profile_id', 'id');
    }
}
