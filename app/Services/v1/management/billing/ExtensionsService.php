<?php

namespace App\Services\v1\management\billing;

use App\DTOs\v1\management\billing\extensions\ExtensionDTO;
use App\Models\Billing\InvoiceExtensionModel;
use App\Models\Billing\InvoiceModel;
use Carbon\Carbon;

class ExtensionsService
{
    /***
     * Muestra las extensiones de una factura
     * @param int $invoiceId
     * @return array
     */
    public function invoiceExtensionData(int $invoiceId): array
    {
        $data = [];
        $invoice = InvoiceModel::with('extensions.user')->findOrFail($invoiceId);
        $data['period'] = $invoice->period?->name;
        $data['extensions'] = $invoice->extensions;

        return $data;
    }

    /***
     * Almacena nueva extensión
     * @param ExtensionDTO $dto
     * @return InvoiceExtensionModel
     */
    public function createExtension(ExtensionDTO $dto): InvoiceExtensionModel
    {
        $newDate = $dto->previousDueDate->copy()->addDays($dto->days);

        return InvoiceExtensionModel::query()
            ->create([
                'invoice_id' => $dto->invoiceId,
                'previous_due_date' => $dto->previousDueDate,
                'extended_due_date' => $newDate,
                'reason' => $dto->reason,
                'user_id' => $dto->user_id,
                'status_id' => $dto->status_id,
            ]);
    }

    /***
     * Retorna datos pertenecientes a una extensión
     * @param int $id
     * @return array
     */
    public function extensionData(int $id): array
    {
        $invoice = InvoiceExtensionModel::query()->findOrFail($id);
        $end = Carbon::parse($invoice->previous_due_date);
        $extended = Carbon::parse($invoice->extended_due_date);
        $days = $end->diffInDays($extended);

        return [
            'previous_due_date' => $end->toDateString(),
            'days' => $days,
            'status_id' => $invoice->status_id,
            'reason' => $invoice->reason,
        ];
    }

    /***
     * Actualizar extensión según id
     * @param InvoiceExtensionModel $model
     * @param ExtensionDTO $dto
     * @return InvoiceExtensionModel
     */
    public function updateExtension(InvoiceExtensionModel $model, ExtensionDTO $dto): InvoiceExtensionModel
    {
        $newDate = $dto->previousDueDate->copy()->addDays($dto->days);

        $model->update([
            'previous_due_date' => $dto->previousDueDate,
            'extended_due_date' => $newDate,
            'reason' => $dto->reason,
            'user_id' => $dto->user_id,
            'status_id' => $dto->status_id,
        ]);

        return $model->refresh();
    }
}
