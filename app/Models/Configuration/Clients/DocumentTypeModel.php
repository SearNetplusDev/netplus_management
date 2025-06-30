<?php

namespace App\Models\Configuration\Clients;

use Illuminate\Database\Eloquent\Model;
use App\Traits\DataViewer;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasStatusTrait;

class DocumentTypeModel extends Model
{
    use SoftDeletes, DataViewer, HasStatusTrait;

    protected $connection = 'pgsql';
    protected $table = 'config_document_types';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'code', 'status_id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = ['id', 'name', 'code', 'status_id'];
    protected array $orderable = ['id', 'name', 'code', 'status_id'];
    protected $appends = ['status'];
}
