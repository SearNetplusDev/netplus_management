<?php

namespace App\Models\Management;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DataViewer;
use App\Traits\HasStatusTrait;

class TechnicianModel extends Model
{
    use SoftDeletes, DataViewer, HasStatusTrait;

    protected $connection = 'pgsql';
    protected $table = 'technicians';
    protected $primaryKey = 'id';
    protected $fillable = ['user_id', 'phone_number', 'hiring_date', 'status_id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = ['id', 'user_id', 'status_id', 'user.name'];
    protected array $orderable = ['id', 'user_id', 'status_id'];
    protected $appends = ['status'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
