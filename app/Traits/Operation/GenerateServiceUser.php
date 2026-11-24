<?php

namespace App\Traits\Operation;

use App\Models\Services\ServiceInternetModel;
use Illuminate\Support\Facades\DB;
use LogicException;

trait GenerateServiceUser
{
    /**
     * Genera el siguiente PPPoE user para un nodo: NetPlus{prefix}{correlativo de 5 dígitos}.
     * Toma el mayor correlativo existente (incluyendo soft_deletes) y le suma 1.
     * Debe llamarse dentro de una DB::transaction: el lock se libera al hacer commit/rollback.
     *
     * @param string $prefix
     * @param int $nodeId
     * @return string
     */
    protected function generateUser(string $prefix, int $nodeId): string
    {
        $connection = DB::connection('pgsql');

        if ($connection->transactionLevel() === 0)
            throw new LogicException('generateUser() debe ejecutarse dentro de una transacción.');

        $base = "NetPlus{$prefix}";
        $length = strlen($base);
        $offset = $length + 1;

        //  Serializa la generación por nodo hasta el fin de la transacción.
        $connection->select("SELECT pg_advisory_xact_lock(7001, ?)", [$nodeId]);

        $max = ServiceInternetModel::withTrashed()
            ->whereRaw("LEFT(\"user\", {$length}) = ?", [$base])
            ->whereRaw("SUBSTRING(\"user\" FROM {$offset}) ~ '^[0-9]+$'")
            ->selectRaw("MAX(CAST(SUBSTRING(\"user\" FROM {$offset}) AS INTEGER)) AS max_seq")
            ->value('max_seq');

        return sprintf('%s%05d', $base, ((int)$max) + 1);
    }
}
