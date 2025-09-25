<?php

namespace App\Models\Clients\Logs;

use App\Models\Clients\ClientModel;
use App\Models\Clients\ReferenceModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferenceLogModel extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'clients_references_logs';
    protected $primaryKey = 'id';
    protected $fillable = ['reference_id', 'client_id', 'user_id', 'action', 'before', 'after'];
    protected $hidden = ['updated_at'];
    protected $casts = [
        'before' => 'array',
        'after' => 'array',
    ];

    public function reference(): BelongsTo
    {
        return $this->belongsTo(ReferenceModel::class, 'reference_id', 'id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientModel::class, 'client_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
