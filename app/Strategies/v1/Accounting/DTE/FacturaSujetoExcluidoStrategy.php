<?php

namespace App\Strategies\v1\Accounting\DTE;

use App\Enums\v1\Billing\DocumentTypes;
use App\Models\Clients\ClientModel;

class FacturaSujetoExcluidoStrategy extends BaseDTEStrategy
{
    /***
     * @return true[]
     */
    protected function identificacionSchema(): array
    {
        return [
            'version' => true,
            'ambiente' => true,
            'tipoDte' => true,
            'numeroControl' => true,
            'codigoGeneracion' => true,
            'tipoModelo' => true,
            'tipoOperacion' => true,
            'tipoContingencia' => true,
            'motivoContin' => true,
            'fecEmi' => true,
            'horEmi' => true,
            'tipoMoneda' => true,
        ];
    }

    /***
     * @return array
     */
    protected function emisorSchema(): array
    {
        return [
            'nit' => true,
            'nrc' => true,
            'nombre' => true,
            'codActividad' => true,
            'descActividad' => true,
            'nombreComercial' => false,
            'tipoEstablecimiento' => false,
            'direccion' => true,
            'telefono' => true,
            'codEstableMH' => true,
            'codEstable' => true,
            'codPuntoVentaMH' => true,
            'codPuntoVenta' => true,
            'correo' => true,
        ];
    }

    /***
     *  Carga los datos del cliente, y construye el DTE.
     *
     * @param array $data
     * @return array
     * @throws \Random\RandomException
     */
    protected function buildBody(array $data): array
    {
        $client = $this->loadClient((int)$data['client_id']);
        $retainedIva = (($data['totals']['iva_retenido'] ?? 0) > 0) || (bool)($client->corporate_info?->retained_iva ?? false);
        $discount = round((float)($data['totals']['discount'] ?? 0), 2);
        [$body, $compra] = $this->buildFromItems($data['items']);

        return $this->assembleDocument(
            client: $client,
            body: $body,
            compra: $compra,
            retainedIva: $retainedIva,
            discount: $discount,
            condition: (int)$data['payment_condition'],
            method: $this->paymentMethodCode((int)$data['payment_method']),
        );
    }

    /***
     * Carga el cliente con las relaciones necesarias para el campo "sujetoExcluido"
     *
     * @param int $clientId
     * @return ClientModel
     */
    protected function loadClient(int $clientId): ClientModel
    {
        return ClientModel::query()
            ->with([
                'dui.document_type',
                'address.state',
                'address.municipality',
                'mobile',
                'email',
                'corporate_info',
                'corporate_info.state',
                'corporate_info.municipality',
            ])
            ->findOrFail($clientId);
    }

    /***
     * Construye las líneas del "cuerpoDocumento" a partir de los elementos ingresados manualmente.
     *
     * @param array $items
     * @return array
     */
    private function buildFromItems(array $items): array
    {
        $compra = 0;
        $body = [];

        foreach ($items as $item) {
            $precioUni = (float)$item['unit_price'];
            $purchase = $precioUni * (int)$item['quantity'];

            $body[] = [
                'numItem' => (int)$item['_line'],
                'tipoItem' => (int)$item['item_type'],
                'cantidad' => (int)$item['quantity'],
                'codigo' => null,
                'uniMedida' => 99,
                'descripcion' => $item['description'],
                'precioUni' => $precioUni,
                'montoDescu' => 0,
                'compra' => $purchase,
            ];
            $compra += $purchase;
        }

        return [$body, $compra];
    }

    /***
     * Ensambla la estructura completa del DTE.
     *
     * @param ClientModel $client
     * @param array $body
     * @param float $compra
     * @param bool $retainedIva
     * @param float $discount
     * @param int $condition
     * @param string|null $method
     * @return array
     * @throws \Random\RandomException
     */
    private function assembleDocument(
        ClientModel $client,
        array       $body,
        float       $compra,
        bool        $retainedIva,
        float       $discount,
        int         $condition,
        ?string     $method,
    ): array
    {
        return [
            'identificacion' => $this->identificacion(DocumentTypes::FACTURA_SUJETO_EXCLUIDO),
            'emisor' => $this->emisor(),
            'sujetoExcluido' => $this->buildSujetoExcluido($client),
            'cuerpoDocumento' => $body,
            'resumen' => $this->buildResumen(
                compra: $compra,
                retainedIva: $retainedIva,
                discount: $discount,
                condition: $condition,
                method: $method ?? '01',
            ),
            'apendice' => null,
        ];
    }

    /***
     * Construye el bloque "sujetoExcluido" con los datos del cliente.
     *
     * @param ClientModel $client
     * @return array
     */
    private function buildSujetoExcluido(ClientModel $client): array
    {
        $fi = $client->corporate_info;

        return [
            'tipoDocumento' => $fi ? 36 : $client->dui?->document_type?->code,
            'numDocumento' => $fi ? $this->parseNumber($fi->nit) : $this->parseNumber($client->dui?->number),
            'nombre' => $fi ? $fi?->invoice_alias : "{$client->name} {$client->surname}",
            'codActividad' => $fi?->activity?->code ?? null,
            'descActividad' => $fi?->activity?->name ?? null,
            'direccion' => [
                'departamento' => $fi?->state?->code ?? $client->address?->state?->code,
                'municipio' => $fi?->municipality?->code ?? $client->address?->municipality?->code,
                'complemento' => $fi?->address ?? $client->address?->address,
            ],
            'telefono' => $this->phoneFormatter($fi?->phone_number ?? $client->mobile?->number) ?? null,
            'correo' => $client->email?->email ?? null,
        ];
    }

    /***
     * Construye el bloque "resumen" con su respectiva lógica financiera.
     *
     * @param float $compra
     * @param bool $retainedIva
     * @param float $discount
     * @param int $condition
     * @param string|null $method
     * @return array
     */
    private function buildResumen(
        float   $compra,
        bool    $retainedIva,
        float   $discount,
        int     $condition,
        ?string $method,
    ): array
    {
        $totalConDescuento = $compra - $discount;
        $neto = $totalConDescuento / parent::TASA_VALOR_NETO;
        $iva = $neto * parent::TASA_IVA;

        //  IVA retenido: 1% del valor neto si aplica y el total supera los $100
        $ivaRetenido = ($retainedIva && $totalConDescuento > 100) ? $neto * parent::TASA_IVA_RETENIDO : 0;
        $totalPagar = $neto + $iva - $ivaRetenido;

        return [
            'totalCompra' => round($compra, 2),
            'descu' => round($discount, 2),
            'totalDescu' => round($discount, 2),
            'subTotal' => round($compra, 2),
            'ivaRete1' => round($ivaRetenido, 2),
            'reteRenta' => 0,
            'totalPagar' => round($totalPagar, 2),
            'totalLetras' => $this->numberToLetter->convert(round($totalPagar, 2)),
            'condicionOperacion' => $condition,
            'pagos' => [
                [
                    'codigo' => $method,
                    'montoPago' => round($totalPagar, 2),
                    'referencia' => null,
                    'plazo' => null,
                    'periodo' => null,
                ],
            ],
            'observaciones' => null,
        ];
    }
}
