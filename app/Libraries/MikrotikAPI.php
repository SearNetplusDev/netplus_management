<?php

namespace App\Libraries;

use Illuminate\Validation\ValidationException;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Exceptions\BadCredentialsException;
use RouterOS\Exceptions\ConfigException;
use RouterOS\Exceptions\ConnectException;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\QueryException;
use RouterOS\Query;
use Throwable;
use function Symfony\Component\Translation\t;

class MikrotikAPI
{
    private const DEFAULT_PORT = 8728;
    private const TIMEOUT = 5;
    private const ATTEMPTS = 1;
    private ?Client $client = null;

    /***
     * @param string $host
     * @param string $user
     * @param string $pass
     * @param int $port
     * @return Config
     * @throws ConfigException
     */
    private function createConfig(string $host, string $user, string $pass, int $port = self::DEFAULT_PORT): Config
    {
        return new Config([
            'host' => $host,
            'user' => $user,
            'pass' => $pass,
            'port' => $port,
            'timeout' => self::TIMEOUT,
            'attempts' => self::ATTEMPTS,
        ]);
    }

    /***
     * @param string $host
     * @param string $user
     * @param string $pass
     * @param int $port
     * @return void
     * @throws ClientException
     * @throws ConfigException
     * @throws BadCredentialsException
     * @throws ConnectException
     * @throws QueryException
     */
    private function connect(string $host, string $user, string $pass, int $port = self::DEFAULT_PORT): void
    {
        $config = $this->createConfig($host, $user, $pass, $port);
        $this->client = new Client($config);
    }

    /***
     * @return void
     */
    private function disconnect(): void
    {
        $this->client = null;
    }

    /***
     * @param string $host
     * @param string $user
     * @param string $pass
     * @param callable $action
     * @param int $port
     * @return mixed
     * @throws ConfigException
     * @throws ClientException
     */
    public function performActionAndClose(
        string   $host,
        string   $user,
        string   $pass,
        callable $action,
        int      $port = self::DEFAULT_PORT
    ): mixed
    {
        try {
            $this->connect($host, $user, $pass, $port);
            return $action($this->client);
        } catch (BadCredentialsException|ConnectException|QueryException $e) {
            throw ValidationException::withMessages([
                'operation' => "Ha ocurrido un error al conectarse al equipo de Mikrotik. {$e->getMessage()}",
            ]);
        } finally {
            $this->disconnect();
        }
    }

    /***
     * @param string $host
     * @param string $user
     * @param string $pass
     * @param int $port
     * @return array
     * @throws ClientException
     * @throws ConfigException
     * @throws QueryException
     */
    public function listPPPPoeProfiles(
        string $host,
        string $user,
        string $pass,
        int    $port = self::DEFAULT_PORT
    ): array
    {
        return $this->performActionAndClose($host, $user, $pass, function (Client $client) {
            $query = new Query('/ppp/profile/print');
            return $client->query($query)->read();
        }, $port);
    }

    /***
     * @param string $host
     * @param string $user
     * @param string $pass
     * @param string $endpoint
     * @param array $where
     * @param int $port
     * @return array
     * @throws ClientException
     * @throws ConfigException
     * @throws QueryException
     */
    public function executeQuery(
        string $host,
        string $user,
        string $pass,
        string $endpoint,
        array  $where = [],
        int    $port = self::DEFAULT_PORT
    ): array
    {
        return $this->performActionAndClose($host, $user, $pass, function (Client $client) use ($endpoint, $where) {
            $query = new Query($endpoint);

            foreach ($where as $key => $value) {
                $query->where($key, $value);
            }
            return $client->query($query)->read();
        }, $port);
    }

    /***
     * @param string $host
     * @param string $user
     * @param string $pass
     * @param array $secretData
     * @param int $port
     * @return array
     * @throws ClientException
     * @throws ConfigException
     * @throws QueryException
     */
    public function createPPPSecret(
        string $host,
        string $user,
        string $pass,
        array  $secretData,
        int    $port = self::DEFAULT_PORT
    ): array
    {
        return $this->performActionAndClose($host, $user, $pass, function (Client $client) use ($secretData) {
            $query = (new Query('/ppp/secret/add'))
                ->equal('name', $secretData['name'])
                ->equal('password', $secretData['password'])
                ->equal('service', $secretData['service'])
                ->equal('profile', $secretData['profile'])
                ->equal('comment', $secretData['comment'])
                ->equal('disabled', $secretData['disabled']);

            return $client->query($query)->read();
        }, $port);
    }
}
