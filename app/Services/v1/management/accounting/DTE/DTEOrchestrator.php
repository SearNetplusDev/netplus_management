<?php

namespace App\Services\v1\management\accounting\DTE;

use App\DTOs\v1\management\accounting\dte\DTEDTO;
use App\Enums\v1\Accounting\InvoiceCategories;
use App\Enums\v1\Billing\DocumentTypes;
use App\Mail\DTE\SendDTEMail;
use App\Models\Accounting\DTEModel;
use App\Models\Billing\PaymentModel;
use App\Services\v1\management\billing\otherInvoices\OtherInvoiceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

readonly class DTEOrchestrator
{
    public function __construct(
        private DTEService          $dteService,
        private DTEPrintService     $dtePrintService,
        private OtherInvoiceService $otherInvoiceService,
    )
    {
    }

    /***
     * @param int $documentId
     * @param array $data
     * @return DTEModel
     * @throws Throwable
     */
    public function process(int $documentId, array $data)/*: DTEModel*/
    {
        $json = $this->dteService->generate($documentId, $data);

        return $json;
        $source = $data['source'] ?? 'payment';
        $paymentId = null;
        $clientId = $data['client_id'] ?? null;
        $invoiceIds = null;
        $otherInvoiceId = null;
        $category = InvoiceCategories::INVOICE;
        $userId = Auth::id() ?? throw new \RuntimeException("Usuario no autenticado");
        $type = DocumentTypes::from($documentId);

        switch ($source) {
            //  Escenario 1: Pago registrado.
            case 'payment':
                $payment = PaymentModel::query()->with('invoices')->findOrFail($data['payment']);
                $clientId = $payment->client_id;
                $paymentId = $payment->id;
                $invoiceIds = $payment->invoices->pluck('id')->toArray();
                break;

            //  Escenario 2: Selección de facturas.
            case 'invoices':
                $invoiceIds = $data['items'];
                break;

            //  Escenario 3: Llenado de formulario
            case 'manual':
                $otherInvoice = $this->otherInvoiceService->createFromManualData(
                    type: $documentId,
                    data: $data,
                    userId: $userId,
                );
                $otherInvoiceId = $otherInvoice->id;
                $category = InvoiceCategories::OTHER_INVOICE;
                break;

            case 'invalidation':
                break;

            default:
                throw new \InvalidArgumentException("Origen no soportado: {$source}");
        }

        $dto = new DTEDTO(
            client_id: (int)$clientId,
            document_type_id: $documentId,
            control_number: $json['identificacion']['numeroControl'],
            generation_code: $json['identificacion']['codigoGeneracion'],
            reception_stamp: strtoupper(Str::random(40)),
            generation_datetime: Carbon::now(),
            total_amount: (float)$json['resumen'][$type->totalAmountKey()],
            payment_id: $paymentId,
            invoice_category: $category,
            invoice_ids: $invoiceIds,
            other_invoice_id: $otherInvoiceId,
            user_id: $userId,
            status_id: true,
            json_body: $json,
        );

        $dte = $this->dteService->storeDTE($dto);
        $this->storeJsonFile($dte);
        $this->sendNotificationMail($dte);

        return $dte;
    }

    /***
     * Almacena el JSON del DTE como archivo .json
     * Ruta de almacenamiento storage/app/dte/json/{año}/{tipo}/{numero_control}.json
     *
     * @param DTEModel $dteModel
     * @return void
     */
    private function storeJsonFile(DTEModel $dteModel): void
    {
        try {
            $year = $dteModel->generation_datetime->year;
            $filename = $this->safeFileName($dteModel->control_number) . '.json';
            $type = DocumentTypes::from($dteModel->document_type_id);
            $folderType = $type->folderName();
            $path = "dte/json/{$year}/{$folderType}/{$filename}";
            $content = json_encode($dteModel->json_body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $store = Storage::disk('s3')->put($path, $content);

            if (!$store) {
                Log::channel('dte_storage')
                    ->error("[DTE] Error al guardar el archivo {$path}");
            }

//            return $path;
        } catch (Throwable $e) {
            Log::channel('dte_storage')
                ->error("[DTE] Error al almacenar el JSON en el disco", [
                    'dte_id' => $dteModel->id,
                    'error' => $e->getMessage(),
                ]);

//            return null;
        }
    }

    /***
     * Genera el pdf, construye el Mailable y lo despacha a cola.
     *
     * @param DTEModel $dteModel
     * @return void
     */
    private function sendNotificationMail(DTEModel $dteModel): void
    {
        try {
            $recipientEmail = $this->resolveRecipientEmail($dteModel);

            if (!$recipientEmail) {
                Log::channel('dte_mail')
                    ->warning("[DTE] Sin correo para notificar", ['dte_id' => $dteModel->id]);
                return;
            }

            $pdf = $this->dtePrintService->print($dteModel->id);
            $pdfRaw = $pdf->output();
            $jsonContent = json_encode($dteModel->json_body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            Mail::to($recipientEmail)
                ->send(new SendDTEMail(
                    dteModel: $dteModel,
                    pdfOutput: $pdfRaw,
                    jsonContent: $jsonContent,
                ));
        } catch (Throwable $e) {
            Log::channel('dte_mail')
                ->error("[DTE] Error al enviar el correo de notificacion", [
                    'dte_id' => $dteModel->id,
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

        return /**$client?->email?->email ??*/ 'sromero.netplus@gmail.com';
    }

    /***
     * Sanitiza el número de control para usarlo como nombre de archivo seguro.
     *
     * @param string $controlNumber
     * @return string
     */
    private function safeFileName(string $controlNumber): string
    {
        return str_replace(['/', '\\', ' ', ':'], '-', $controlNumber);
    }
}
