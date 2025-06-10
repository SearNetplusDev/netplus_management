<?php

namespace App\Models\Configuration;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuModel extends Model
{
    use SoftDeletes;

    protected $connection = 'pgsql';
    protected $table = 'config_menu';
    protected $fillable = ['name', 'url', 'icon', 'parent_id', 'order', 'status_id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuModel::class, 'parent_id')
            ->where('status_id', 1);
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuModel::class, 'parent_id')
            ->where('status_id', 1)
            ->orderBy('order');
    }
}
