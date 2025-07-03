<?php

namespace App\Models\Clients;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DataViewer;
use App\Traits\HasStatusTrait;

class DocumentModel extends Model
{
    use SoftDeletes, DataViewer, HasStatusTrait;

    protected $connection = 'pgsql';
    protected $table = 'clients_documents';
    protected $primaryKey = 'id';
    protected $fillable = ['client_id', 'document_type_id', 'number', 'expiration_date', 'status_id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = ['id', 'client_id', 'document_type_id', 'number', 'status_id'];
    protected array $orderable = ['id', 'client_id', 'document_type_id', 'number', 'status_id'];
    protected $appends = ['status'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientModel::class, 'client_id', 'id');
    }
}
