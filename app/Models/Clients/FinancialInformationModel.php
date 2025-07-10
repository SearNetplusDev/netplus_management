<?php

namespace App\Models\Clients;

use App\Models\Billing\Options\ActivityModel;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasStatusTrait;
use App\Traits\DataViewer;

class FinancialInformationModel extends Model
{
    use DataViewer, HasStatusTrait, SoftDeletes;

    protected $connection = 'pgsql';
    protected $table = 'clients_financial_information';
    protected $primaryKey = 'id';
    protected $fillable = [
        'client_id',
        'nrc',
        'activity_id',
        'retained_iva',
        'legal_representative',
        'dui',
        'nit',
        'phone_number',
        'invoice_alias',
        'state_id',
        'municipality_id',
        'district_id',
        'address',
        'status_id',
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = [
        'id',
        'client_id',
        'nrc',
        'activity_id',
        'retained_iva',
        'legal_representative',
        'dui',
        'nit',
        'phone_number',
        'invoice_alias',
        'state_id',
        'municipality_id',
        'district_id',
        'address',
        'status_id',
    ];
    protected array $orderable = [
        'id',
        'client_id',
        'nrc',
        'activity_id',
        'retained_iva',
        'legal_representative',
        'dui',
        'nit',
        'phone_number',
        'invoice_alias',
        'state_id',
        'municipality_id',
        'district_id',
        'address',
        'status_id',
    ];
    protected $appends = ['status'];

    public function activity(): HasOne
    {
        return $this->hasOne(ActivityModel::class, 'id', 'activity_id');
    }
}
