<?php

namespace App\Services\v1\management\accounting\DTE;

use App\Mail\DTE\SendCancelDTEMail;
use App\Mail\DTE\SendDTEMail;
use App\Models\Accounting\CancelDTEModel;
use App\Models\Accounting\DTEModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

readonly class DTEMailService
{
    public function __construct(private DTEPrintService $dtePrintService)
    {
    }

    /****
     * Genera el pdf, construye el mailable y lo despacha.
     *
     * @param DTEModel $dteModel
     * @return bool
     */
    public function sendDTEMail(DTEModel $dteModel): bool
    {
        try {
            $recipientEmail = $this->resolveRecipientEmail($dteModel);

            if (!$recipientEmail) {
                Log::channel('dte_mail')
                    ->warning("[DTE] Sin correo para notificar", ['dte_id' => $dteModel->id]);
                return false;
            }

            $pdf = $this->dtePrintService->print($dteModel->id);
            $pdfRaw = $pdf->output();
            $jsonContent = $this->encodeJson($dteModel->json_body);

            Mail::to($recipientEmail)
                ->send(new SendDTEMail(
                    dteModel: $dteModel,
                    pdfOutput: $pdfRaw,
                    jsonContent: $jsonContent
                ));

            return true;
        } catch (Throwable $e) {
            Log::channel('dte_mail')
                ->error("[DTE] Error al enviar el correo de notificación", [
                    'dte_id' => $dteModel->id,
                    'error' => $e->getMessage(),
                ]);
            return false;
        }
    }

    /***
     * Envía el correo de notificación cuando ocurre una anulación.
     *
     * @param CancelDTEModel $cancelDTEModel
     * @return void
     */
    public function sendInvalidationMail(CancelDTEModel $cancelDTEModel): void
    {
        try {
            $originalDte = DTEModel::query()
                ->with('client.email')
                ->findOrFail($cancelDTEModel->dte_id);

            $recipientEmail = $this->resolveRecipientEmail($originalDte);

            if (!$recipientEmail) {
                Log::channel('dte_mail')
                    ->warning("[DTE] Sin correo para notificar anulación", [
                        'invalidation_id' => $cancelDTEModel->id
                    ]);
                return;
            }

            Mail::to($recipientEmail)
                ->send(new SendCancelDTEMail(
                    invalidation: $cancelDTEModel,
                    originalDte: $originalDte
                ));
        } catch (Throwable $e) {
            Log::channel('dte_mail')
                ->error("[DTE] Error al enviar el correo de anulación", [
                    'invalidation_id' => $cancelDTEModel->id,
                    'error' => $e->getMessage(),
                ]);
        }
    }

    /***
     * Resuelve el correo del destinatario según el tipo de cliente.
     *
     * @param DTEModel $dteModel
     * @return string|null
     */
    private function resolveRecipientEmail(DTEModel $dteModel): ?string
    {
        $client = $dteModel->relationLoaded('client')
            ? $dteModel->client
            : $dteModel->client()->with('email')->first();

//        return $client->email?->email;
        return 'sromero.netplus@gmail.com';
    }

    /***
     * Codifica un array a JSON con formato legible.
     *
     * @param array $data
     * @return string
     */
    private function encodeJson(array $data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
