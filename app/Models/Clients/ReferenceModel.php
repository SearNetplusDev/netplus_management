<?php

namespace App\Models\Clients;

use App\Models\Configuration\Clients\KinshipModel;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DataViewer;
use App\Traits\HasStatusTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReferenceModel extends Model
{
    use DataViewer, HasStatusTrait, SoftDeletes;

    protected $connection = 'pgsql';
    protected $table = 'clients_references';
    protected $primaryKey = 'id';
    protected $fillable = ['client_id', 'name', 'dui', 'mobile', 'kinship_id', 'status_id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = ['id', 'client_id', 'name', 'dui', 'mobile', 'kinship_id', 'status_id'];
    protected array $orderable = ['id', 'client_id', 'name', 'dui', 'mobile', 'kinship_id', 'status_id'];
    protected $appends = ['status'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientModel::class, 'client_id', 'id');
    }

    public function kinship(): HasOne
    {
        return $this->hasOne(KinshipModel::class, 'id', 'kinship_id');
    }
}
