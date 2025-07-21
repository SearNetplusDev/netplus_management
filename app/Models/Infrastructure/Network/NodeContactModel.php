<?php

namespace App\Models\Infrastructure\Network;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DataViewer;
use App\Traits\HasStatusTrait;

class NodeContactModel extends Model
{
    use SoftDeletes, DataViewer, HasStatusTrait;

    protected $connection = 'pgsql';
    protected $table = 'infrastructure_nodes_contacts';
    protected $primaryKey = 'id';
    protected $fillable = [
        'node_id',
        'name',
        'phone_number',
        'initial_contract_date',
        'final_contract_date',
        'status_id'
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected array $allowedFilters = ['id', 'node_id', 'name', 'phone_number', 'status_id'];
    protected array $orderable = ['id', 'node_id', 'name', 'phone_number', 'status_id'];
    protected $appends = ['status', 'diff_days'];

    public function node(): BelongsTo
    {
        return $this->belongsTo(NodeModel::class, 'node_id', 'id');
    }

    public function getDiffDaysAttribute(): ?int
    {
        if (!$this->final_contract_date)
            return null;
        try {

            $today = Carbon::today();
            $end = Carbon::parse($this->final_contract_date);
            return $today->diffInDays($end, false);
        } catch (\Exception $e) {
            return null;
        }
    }
}
