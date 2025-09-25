<?php

namespace App\Models\Clients;

use App\Models\Configuration\Clients\PhoneTypeModel;
use App\Models\Configuration\Geography\CountryModel;
use App\Observers\Clients\PhoneObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DataViewer;
use App\Traits\HasStatusTrait;

#[ObservedBy(PhoneObserver::class)]
class PhoneModel extends Model
{
    use SoftDeletes, DataViewer, HasStatusTrait;

    protected $connection = 'pgsql';
    protected $table = 'clients_phones';
    protected $primaryKey = 'id';
    protected $fillable = ['client_id', 'phone_type_id', 'number', 'status_id', 'country_code'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = ['id', 'client_id', 'phone_type_id', 'number', 'status_id', 'country_code'];
    protected array $orderable = ['id', 'client_id', 'phone_type_id', 'number', 'status_id', 'country_code'];
    protected $appends = ['status'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientModel::class, 'client_id', 'id');
    }

    public function phone_type(): BelongsTo
    {
        return $this->belongsTo(PhoneTypeModel::class, 'phone_type_id', 'id');
    }

    public function country(): HasOne
    {
        return $this->hasOne(CountryModel::class, 'iso_2', 'country_code');
    }
}
