<?php

namespace App\Models\Clients\Logs;

use App\Models\Clients\ClientModel;
use App\Models\Clients\EmailModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLogModel extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'clients_emails_logs';
    protected $primaryKey = 'id';
    protected $fillable = ['email_id', 'client_id', 'user_id', 'action', 'before', 'after'];
    protected $hidden = ['updated_at'];
    protected $casts = [
        'before' => 'array',
        'after' => 'array',
    ];

    public function email(): BelongsTo
    {
        return $this->belongsTo(EmailModel::class, 'email_id', 'id');
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
