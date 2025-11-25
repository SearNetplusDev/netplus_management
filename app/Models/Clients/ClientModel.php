<?php

namespace App\Models\Clients;

use App\Enums\v1\General\CommonStatus;
use App\Models\Billing\Options\DocumentTypeModel;
use App\Models\Configuration\BranchModel;
use App\Models\Configuration\Clients\ClientTypeModel;
use App\Models\Configuration\Clients\GenderModel;
use App\Models\Configuration\Clients\MaritalStatusModel;
use App\Models\Configuration\Geography\CountryModel;
use App\Models\Services\ServiceModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DataViewer;
use App\Traits\HasStatusTrait;
use App\Observers\Clients\ClientObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([ClientObserver::class])]
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
        'dui.number'
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

    public function getPrimaryDocumentAttribute()
    {
        $document = $this->dui ?? $this->nit ?? $this->passport ?? $this->residence;

        if (!$document) {
            return null;
        }

        return (object)[
            'type' => $document->document_type->name ?? 'Documento',
            'number' => $document->number,
        ];
    }

    public function gender(): BelongsTo
    {
        return $this->belongsTo(GenderModel::class, 'gender_id', 'id');
    }

    public function marital_status(): BelongsTo
    {
        return $this->belongsTo(MaritalStatusModel::class, 'marital_status_id', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(BranchModel::class, 'branch_id', 'id');
    }

    public function client_type(): BelongsTo
    {
        return $this->belongsTo(ClientTypeModel::class, 'client_type_id', 'id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(CountryModel::class, 'country_id', 'id');
    }

    public function document_type(): BelongsTo
    {
        return $this->belongsTo(DocumentTypeModel::class, 'document_type_id', 'id');
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

    public function passport(): HasOne
    {
        return $this->hasOne(DocumentModel::class, 'client_id', 'id')
            ->where('document_type_id', 2);
    }

    public function residence(): HasOne
    {
        return $this->hasOne(DocumentModel::class, 'client_id', 'id')
            ->where('document_type_id', 1);
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

    public function address(): HasOne
    {
        return $this->hasOne(AddressModel::class, 'client_id', 'id')
            ->where('status_id', 1)
            ->latest();
    }

    public function services(): HasMany
    {
        return $this->hasMany(ServiceModel::class, 'client_id', 'id');
    }

    public function active_services(): HasMany
    {
        return $this->hasMany(ServiceModel::class, 'client_id', 'id')
            ->where('status_id', CommonStatus::ACTIVE->value);
    }
}
