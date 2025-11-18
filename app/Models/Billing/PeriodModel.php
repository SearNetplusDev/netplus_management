<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PeriodModel extends Model
{
    use SoftDeletes;

    protected $connection = 'pgsql';
    protected $table = 'billing_periods';
    protected $primaryKey = 'id';
    protected $fillable = [
        'name',             //  Nombre (diciembre 2025)
        'code',             //  Código
        'period_start',     //  Inicio de período
        'period_end',       //  Fin del período
        'issue_date',       //  Fecha de emisión
        'cutoff_date',      //  Fecha de corte
        'is_active',        //  Período activo
        'is_closed',        //  Período procesado
        'closed_at',        //  Fecha de cierre del período
        'status_id',        //  Estado del período
        'comments',         //  Comentarios
    ];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'issue_date' => 'date',
        'cutoff_date' => 'date',
        'closed_at' => 'datetime',
        'is_active' => 'boolean',
        'is_closed' => 'boolean',
        'status_id' => 'boolean',
    ];
}
