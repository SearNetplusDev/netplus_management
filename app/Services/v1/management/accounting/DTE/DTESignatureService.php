<?php

namespace App\Services\v1\management\accounting\DTE;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class DTESignatureService
{
    public function __construct(
        private readonly Client $httpClient,
    )
    {
    }

    /****
     * @return object|mixed
     */
    public function auth(): object
    {
        $uri = config('dte.auth_url');

        try {
            $request = $this->httpClient->request('POST', $uri, [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => [
                    'user' => config('dte.user'),
                    'pwd' => config('dte.password'),
                ],
            ]);

            $decoded = json_decode($request->getBody()->getContents());

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException('Respuesta de auth inválida: ', json_last_error_msg());
            }

            return $decoded;
        } catch (GuzzleException $e) {
            Log::error("DTE auth falló", ['error' => $e->getMessage()]);
            throw new RuntimeException("No se pudo autenticar con el servicio DTE.", previous: $e);
        }
    }

    /***
     * @param array $dte
     * @return object|mixed
     */
    public function signDocument(array $dte): object
    {
        $uri = config('dte.signer_url');
        try {
            $request = $this->httpClient->request('POST', $uri, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'nit' => config('dte.nit'),
                    'activo' => true,
                    'passwordPri' => config('dte.password'),
                    'dteJson' => $dte,
                ],
            ]);

            $decoded = json_decode($request->getBody()->getContents());

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException('Respuesta del firmador inválida: ', json_last_error_msg());
            }

            return $decoded;
        } catch (GuzzleException $e) {
            Log::error('Fallo la firma del documento: ', ['error' => $e->getMessage()]);
            throw new RuntimeException('No se pudo firmar el documento.', previous: $e);
        }
    }
}
