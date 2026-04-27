<?php

namespace App\Strategies\v1\Accounting\DTE\Prints;

use App\Contracts\v1\Accounting\DTE\DTEPrinterInterface;
use App\Models\Accounting\DTEModel;
use App\Models\Clients\ClientModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDF;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

abstract readonly class BasePrint implements DTEPrinterInterface
{
    /***
     * Genera el PDF con los datos del documento emitido.
     *
     * @param DTEModel $dte
     * @return DomPDF
     */
    public function print(DTEModel $dte): DomPDF
    {
        return $this->generate($dte);
    }

    abstract protected function generate(DTEModel $model): DomPDF;

    /***
     * Nombre de la vista que se debe renderizar.
     *
     * @return string
     */
    abstract protected function getView(): string;

    /***
     * Datos que serán enviados a la vista.
     *
     * @param DTEModel $model
     * @return array
     */
    protected function buildViewData(DTEModel $model): array
    {
        return [
            'qrCode' => $this->buildQrCode(
                generationCode: $model->generation_code,
                date: $model->generation_datetime
            ),
            'data' => $model->json_body,
            'receptionStamp' => $model->reception_stamp,
        ];
    }

    /***
     * Genera el código QR.
     *
     * @param string $generationCode
     * @param Carbon $date
     * @return string
     */
    protected function buildQrCode(string $generationCode, Carbon $date): string
    {
        $genDate = Carbon::parse($date)->format('Y-m-d');
        $uri = "https://admin.factura.gob.sv/consultaPublica?ambiente=01&codGen={$generationCode}&fechaEmi={$genDate}";

        return base64_encode(
            QrCode::format('svg')
                ->size(120)
                ->errorCorrection('H')
                ->generate($uri)
        );
    }

    /***
     * Retorna los datos del cliente.
     *
     * @param int $clientId
     * @param array $relations
     * @return ClientModel
     */
    protected function getClientInfo(int $clientId, array $relations = []): ClientModel
    {
        return ClientModel::query()
            ->with($relations)
            ->findOrFail($clientId);
    }
}
