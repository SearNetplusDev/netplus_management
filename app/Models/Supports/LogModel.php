<?php

namespace App\Models\Supports;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogModel extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'supports_logs';
    protected $primaryKey = 'id';
    protected $fillable = ['support_id', 'user_id', 'action', 'before', 'after'];
    protected $hidden = ['updated_at'];
    protected $casts = [
        'before' => 'array',
        'after' => 'array'
    ];

    public function support(): BelongsTo
    {
        return $this->belongsTo(SupportModel::class, 'support_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
