<?php

namespace App\Services\v1\network;

use App\Libraries\MikrotikAPI;
use Illuminate\Validation\ValidationException;
use Throwable;

class MikrotikInternetService
{
    /***
     * @param MikrotikAPI $mikrotikAPI
     */
    public function __construct(private MikrotikAPI $mikrotikAPI)
    {

    }

    /***
     *  Create PPPoe Secrets
     * @param array $server
     * @param array $profile
     * @param string $username
     * @param string $password
     * @param string $comment
     * @return void
     */
    public function createUser(
        array  $server,
        array  $profile,
        string $username,
        string $password,
        string $comment
    ): void
    {
        $data = [
            'name' => $username,
            'password' => $password,
            'service' => 'pppoe',
            'profile' => $profile['mk_profile'],
            'comment' => $comment,
            'disabled' => 'no',
        ];

        try {
            $this->mikrotikAPI->createPPPSecret(
                $server['ip'],
                $server['user'],
                $server['secret'],
                $data,
            );
        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'mikrotik' => "Error al crear el usuario PPPoe: {$e->getMessage()}",
            ]);
        }
    }

    /***
     * Update PPPoe Secrets
     * @param array $server
     * @param string $currentUsername
     * @param array $data
     * @return void
     */
    public function updateUser(array $server, string $currentUsername, array $data): void
    {
        try {
            $this->mikrotikAPI->updatePPPSecret(
                $server['ip'],
                $server['user'],
                $server['secret'],
                $currentUsername,
                $data
            );
        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'mikrotik' => "Error al actualizar el usuario PPPoe: {$e->getMessage()}",
            ]);
        }
    }

    /***
     * Enable/Disable PPPoe Secret
     * @param array $server
     * @param string $username
     * @return void
     */
    public function toggleUser(array $server, string $username, bool $disable = true): void
    {
        try {
            $this->mikrotikAPI->togglePPPSecret(
                $server['ip'],
                $server['user'],
                $server['secret'],
                $username,
                $disable
            );

        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'mikrotik' => "Error al activar/desactivar el usuario PPPoe: {$e->getMessage()}",
            ]);
        }
    }

    /***
     * @param array $server
     * @param string $username
     * @return void
     * @throws \RouterOS\Exceptions\ClientException
     * @throws \RouterOS\Exceptions\ConfigException
     */
    public function enableUser(array $server, string $username): void
    {
        $this->mikrotikAPI->togglePPPSecret(
            $server['ip'],
            $server['user'],
            $server['secret'],
            $username,
            false
        );
    }

    /***
     * @param array $server
     * @param string $username
     * @return void
     * @throws \RouterOS\Exceptions\ClientException
     * @throws \RouterOS\Exceptions\ConfigException
     */
    public function disableUser(array $server, string $username): void
    {
        $this->mikrotikAPI->togglePPPSecret(
            $server['ip'],
            $server['user'],
            $server['secret'],
            $username,
            true
        );
    }

    /***
     * Remove PPPoe Secret from Server
     * @param array $server
     * @param string $username
     * @return void
     */
    public function deleteUser(array $server, string $username): void
    {
        try {
            $this->mikrotikAPI->deletePPPSecret(
                $server['ip'],
                $server['user'],
                $server['secret'],
                $username,
            );
        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'mikrotik' => "Error al eliminar el usuario PPPoe: {$e->getMessage()}",
            ]);
        }
    }

    /***
     * @param array $server
     * @return array
     */
    public function listProfiles(array $server): array
    {
        try {
            return $this->mikrotikAPI->listPPPPoeProfiles(
                $server['ip'],
                $server['user'],
                $server['secret'],
            );
        } catch (Throwable $e) {
            throw ValidationException::withMessages([
                'mikrotik' => "Error al listar los propietarios PPPoe.",
            ]);
        }
    }
}
