<?php

namespace App\Models\Services\Logs;

use App\Models\Services\ServiceInternetModel;
use App\Models\Services\ServiceModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternetServiceLogModel extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'internet_services_logs';
    protected $primaryKey = 'id';
    protected $fillable = [
        'internet_service_id',
        'service_id',
        'user_id',
        'action',
        'before',
        'after',
    ];
    protected $hidden = ['updated_at'];
    protected $casts = [
        'before' => 'array',
        'after' => 'array',
    ];

    public function internet_service(): BelongsTo
    {
        return $this->belongsTo(ServiceInternetModel::class, 'internet_service_id', 'id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceModel::class, 'service_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
