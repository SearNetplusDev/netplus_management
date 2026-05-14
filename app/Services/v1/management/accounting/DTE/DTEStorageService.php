<?php

namespace App\Services\v1\management\accounting\DTE;

use App\Enums\v1\Billing\DocumentTypes;
use App\Models\Accounting\CancelDTEModel;
use App\Models\Accounting\DTEModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

readonly class DTEStorageService
{
    /****
     * Almacena el JSON del DTE como archivo .json en S3.
     *
     * @param DTEModel $dteModel
     * @return void
     */
    public function storeDTEJson(DTEModel $dteModel): void
    {
        try {
            $year = $dteModel->generation_datetime->year;
            $type = DocumentTypes::from($dteModel->document_type_id);
            $filename = $this->safeFileName($dteModel->control_number) . '.json';
            $path = "dte/json/{$year}/{$type->folderName()}/{$filename}";
            $content = $this->encodeJson($dteModel->json_body);

            if (!Storage::disk('s3')->put($path, $content)) {
                Log::channel('dte_storage')
                    ->error("[DTE] Error al guardar el archivo", ['path' => $path]);
            }
        } catch (Throwable $e) {
            Log::channel('dte_storage')
                ->error("[DTE] Error al almacenar el JSON en S3", [
                    'dte_id' => $dteModel->id,
                    'error' => $e->getMessage(),
                ]);
        }
    }

    /****
     * Almacena el JSON de anulación en S3.
     *
     * @param CancelDTEModel $cancelDTEModel
     * @return void
     */
    public function storeInvalidationJson(CancelDTEModel $cancelDTEModel): void
    {
        try {
            $dte = $cancelDTEModel->dte;
            $type = DocumentTypes::from($dte->document_type_id);
            $year = $cancelDTEModel->generation_datetime->year;
            $filename = $this->safeFileName($cancelDTEModel->generation_code) . '.json';
            $path = "dte/json/{$year}/ANULACIONES/{$type->folderName()}/{$filename}";
            $content = $this->encodeJson($cancelDTEModel->json_body);

            if (!Storage::disk('s3')->put($path, $content)) {
                Log::channel('dte_storage')
                    ->error("[DTE] Error al guardar el archivo de anulación", ['path' => $path]);
            }
        } catch (Throwable $e) {
            Log::channel('dte_storage')
                ->error("[DTE] Error al almacenar el JSON de anulación en S3", [
                    'invalidation_id' => $cancelDTEModel->id,
                    'error' => $e->getMessage(),
                ]);
        }
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

    /***
     * Sanitiza una cadena para usarla como nombre de archivo seguro.
     *
     * @param string $val
     * @return string
     */
    private function safeFileName(string $val): string
    {
        return str_replace(['/', '\\', ' ', ':'], '-', $val);
    }
}
