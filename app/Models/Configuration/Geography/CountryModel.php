<?php

namespace App\Models\Configuration\Geography;

use App\Traits\DataViewer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CountryModel extends Model
{
    use SoftDeletes, DataViewer;

    protected $connection = 'pgsql';
    protected $table = 'config_countries';
    protected $primaryKey = 'id';
    protected $fillable = ['es_name', 'en_name', 'iso_2', 'iso_3', 'phone_prefix', 'status_id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = ['id', 'es_name', 'en_name', 'iso_2', 'iso_3', 'phone_prefix', 'status_id'];
    protected array $orderable = ['id', 'es_name', 'en_name', 'status_id'];
}
