<?php

namespace App\Models\Clients\Logs;

use App\Models\Clients\ClientModel;
use App\Models\Clients\PhoneModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhoneLogModel extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'clients_phones_logs';
    protected $primaryKey = 'id';
    protected $fillable = ['phone_id', 'client_id', 'user_id', 'action', 'before', 'after'];
    protected $hidden = ['updated_at'];
    protected $casts = [
        'before' => 'array',
        'after' => 'array',
    ];

    public function phone(): BelongsTo
    {
        return $this->belongsTo(PhoneModel::class, 'phone_id', 'id');
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
