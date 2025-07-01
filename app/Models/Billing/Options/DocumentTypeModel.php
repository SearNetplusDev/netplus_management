<?php

namespace App\Models\Billing\Options;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasStatusTrait;
use App\Traits\DataViewer;

class DocumentTypeModel extends Model
{
    use HasStatusTrait, DataViewer, SoftDeletes;

    protected $connection = 'pgsql';
    protected $table = 'billing_document_types';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'code', 'status_id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = ['id', 'name', 'code', 'status_id'];
    protected array $orderable = ['id', 'name', 'code', 'status_id'];
    protected $appends = ['status'];
}
