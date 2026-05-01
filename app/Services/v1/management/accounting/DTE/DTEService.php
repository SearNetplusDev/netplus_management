<?php

namespace App\Services\v1\management\accounting\DTE;

use App\Contexts\Accounting\DTEContext;
use App\DTOs\v1\management\accounting\dte\DTEDTO;
use App\Enums\v1\Accounting\InvoiceCategories;
use App\Enums\v1\Billing\DocumentTypes;
use App\Models\Accounting\DTEModel;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class DTEService
{
    public function __construct(
        private DTEContext $context,
    )
    {
    }

    /***
     * Genera el JSON con el DTE según estrategia.
     *
     * @param int $documentId
     * @param array $data
     * @return array
     * @throws Throwable
     */
    public function generate(int $documentId, array $data): array
    {
        $type = DocumentTypes::from($documentId);
        $this->context->setStrategy($type->strategy());
        return $this->context->execute($data);
    }

    /***
     * Almacena el DTE la tabla.
     *
     * @param DTEDTO $dto
     * @return DTEModel
     */
    public function storeDTE(DTEDTO $dto): DTEModel
    {
        try {
            return DB::transaction(function () use ($dto) {
                $dte = DTEModel::create($dto->toModelAttributes());

                if ($dto->invoice_category === InvoiceCategories::INVOICE && !empty($dto->invoice_ids)) {
                    $dte->invoices()->attach($dto->invoice_ids);
                }

                return $dte;
            });
        } catch (Throwable $e) {
            throw new \InvalidArgumentException("Error al crear DTE", 500, $e);
        }
    }


    /***
     * Realiza la búsqueda de DTE basado en el número de control.
     *
     * @param string $controlNumber
     * @return Collection
     */
    public function search(string $controlNumber): Collection
    {
        $year = Carbon::now()->year;

        return DTEModel::query()
            ->select(['id', 'control_number as label'])
            ->where('control_number', 'ILIKE', '%' . $controlNumber . '%')
            ->whereYear('generation_datetime', $year)
            ->where('status_id', true)
            ->get();
    }
}
