<?php

namespace App\Models\Supports;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DataViewer;

class SupportDetailModel extends Model
{
    use SoftDeletes, DataViewer;

    protected $connection = 'pgsql';
    protected $table = 'supports_details';
    protected $primaryKey = 'id';
    protected $fillable = [
        'support_id',
        'type_id',
        'internet_profile_id',
        'node_id',
        'equipment_id',
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = [
        'id',
        'type_id',
        'internet_profile_id',
        'node_id',
        'equipment_id',
    ];
    protected array $orderable = [
        'id',
        'type_id',
        'internet_profile_id',
        'node_id',
        'equipment_id',
    ];

    public function support(): BelongsTo
    {
        return $this->belongsTo(SupportModel::class, 'support_id', 'id');
    }
}
