<?php

namespace App\Models\Clients\Logs;

use App\Models\Clients\AddressModel;
use App\Models\Clients\ClientModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddressLogModel extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'clients_addresses_logs';
    protected $primaryKey = 'id';
    protected $fillable = ['address_id', 'client_id', 'user_id', 'action', 'before', 'after'];
    protected $hidden = ['updated_at'];
    protected $casts = [
        'before' => 'array',
        'after' => 'array',
    ];

    public function address(): BelongsTo
    {
        return $this->belongsTo(AddressModel::class, 'address_id', 'id');
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
