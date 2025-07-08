<?php

namespace App\Models\Clients;

use App\Models\Billing\Options\DocumentTypeModel;
use App\Models\Configuration\BranchModel;
use App\Models\Configuration\Clients\ClientTypeModel;
use App\Models\Configuration\Clients\GenderModel;
use App\Models\Configuration\Clients\MaritalStatusModel;
use App\Models\Configuration\Geography\CountryModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DataViewer;
use App\Traits\HasStatusTrait;

class ClientModel extends Model
{
    use SoftDeletes, DataViewer, HasStatusTrait;

    protected $connection = 'pgsql';
    protected $table = 'clients';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',
        'surname',
        'gender_id',
        'birthdate',
        'marital_status_id',
        'branch_id',
        'client_type_id',
        'profession',
        'country_id',
        'document_type_id',
        'legal_entity',
        'status_id',
        'comments'
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = [
        'id',
        'name',
        'surname',
        'gender_id',
        'branch_id',
        'client_type_id',
        'status_id',
    ];
    protected array $orderable = [
        'id',
        'name',
        'surname',
        'gender_id',
        'branch_id',
        'client_type_id',
        'status_id',
    ];
    protected $appends = ['status'];

    public function gender(): HasOne
    {
        return $this->hasOne(GenderModel::class, 'id', 'gender_id');
    }

    public function marital_status(): HasOne
    {
        return $this->hasOne(MaritalStatusModel::class, 'id', 'marital_status_id');
    }

    public function branch(): HasOne
    {
        return $this->hasOne(BranchModel::class, 'id', 'branch_id');
    }

    public function client_type(): HasOne
    {
        return $this->hasOne(ClientTypeModel::class, 'id', 'client_type_id');
    }

    public function country(): HasOne
    {
        return $this->hasOne(CountryModel::class, 'id', 'country_id');
    }

    public function billing_document(): HasOne
    {
        return $this->hasOne(DocumentTypeModel::class, 'id', 'document_type_id');
    }

    public function personal_documents(): HasMany
    {
        return $this->hasMany(DocumentModel::class, 'client_id', 'id');
    }

    public function dui(): HasOne
    {
        return $this->hasOne(DocumentModel::class, 'client_id', 'id')
            ->where('document_type_id', 3);
    }

    public function nit(): HasOne
    {
        return $this->hasOne(DocumentModel::class, 'client_id', 'id')
            ->where('document_type_id', 4);
    }

    public function mobile(): HasOne
    {
        return $this->hasOne(PhoneModel::class, 'client_id', 'id')
            ->where('phone_type_id', 2)
            ->latest();
    }

    public function landline(): HasOne
    {
        return $this->hasOne(PhoneModel::class, 'client_id', 'id')
            ->where('phone_type_id', 1)
            ->latest();
    }

    public function email(): HasOne
    {
        return $this->hasOne(EmailModel::class, 'client_id', 'id')
            ->where('status_id', 1)
            ->latest();
    }
}
