<?php

namespace Yxx\LaravelPlugin\Support;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\App;
use Psr\Http\Message\StreamInterface;

class MarketSDK
{
    /**
     * @param  array  $options
     * @return array
     *
     * @throws GuzzleException
     */
    public function upload(array $options): array
    {
        return $this->request('/api/pluginmarket/plugins', 'POST', $options);
    }

    /**
     * @return array
     *
     * @throws GuzzleException
     */
    public function plugins(): array
    {
        return $this->httpGet('/api/pluginmarket/plugins');
    }

    /**
     * @return array
     *
     * @throws GuzzleException
     */
    public function getUserPlugins(): array
    {
        return $this->httpGet('/api/pluginmarket/user/plugins');
    }

    /**
     * @return array
     *
     * @throws GuzzleException
     */
    public function getUserInfo(): array
    {
        return $this->httpGet('/api/pluginmarket/user-info');
    }

    /**
     * @param  string  $email
     * @param  string  $password
     * @return array
     *
     * @throws GuzzleException
     */
    public function login(string $email, string $password): array
    {
        return $this->httpPostJson('/api/pluginmarket/login', compact(
            'email',
            'password'
        ));
    }

    /**
     * @param  string  $name
     * @param  string  $email
     * @param  string  $password
     * @param  string  $passwordConfirmation
     * @return array
     *
     * @throws GuzzleException
     */
    public function register(string $name, string $email, string $password, string $passwordConfirmation): array
    {
        return $this->httpPostJson('/api/pluginmarket/register', [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ]);
    }

    /**
     * @return array
     *
     * @throws GuzzleException
     */
    public function count(): array
    {
        return $this->httpGet('/api/pluginmarket/plugins/count');
    }

    /**
     * @param  int  $versionId
     * @return StreamInterface
     *
     * @throws GuzzleException
     */
    public function install(int $versionId): StreamInterface
    {
        try {
            return $this->client()->request('POST', ltrim('/api/pluginmarket/plugins/install/'.$versionId, '/'))->getBody();
        } catch (ClientException $e) {
            $response = $e->getResponse();
            if ($message = data_get(json_decode($response->getBody()->getContents(), true), 'message')) {
                throw new \Exception($message, $e->getCode());
            }
            if ($message = $response->getReasonPhrase()) {
                throw new \Exception($message, $e->getCode());
            }
            throw $e;
        }
    }

    /**
     * @param  string  $url
     * @param  string  $method
     * @param  array  $options
     * @param  int  $tries
     * @return mixed
     *
     * @throws GuzzleException
     */
    public function request(string $url, string $method = 'GET', array $options = [], $tries = 0): array
    {
        try {
            return json_decode((string) $this->client()->request($method, ltrim($url, '/'), array_filter($options))->getBody(), true);
        } catch (ClientException $e) {
            $response = $e->getResponse();

            if ($response->getStatusCode() === 429 && $response->hasHeader('retry-after') && $tries < 3) {
                $retryAfter = $response->getHeader('retry-after')[0];

                sleep($retryAfter + 1);

                return $this->request($url, $method, $options, $tries + 1);
            }

            if ($message = data_get(json_decode($response->getBody()->getContents(), true), 'message')) {
                throw new \Exception($message, $e->getCode());
            }
            if ($message = $response->getReasonPhrase()) {
                throw new \Exception($message, $e->getCode());
            }
            throw $e;
        }
    }

    /**
     * @param  string  $url
     * @param  array  $data
     * @param  array  $query
     * @return array
     *
     * @throws GuzzleException
     */
    public function httpPostJson(string $url, array $data = [], array $query = []): array
    {
        return $this->request($url, 'POST', ['query' => $query, 'json' => $data]);
    }

    /**
     * @param  string  $url
     * @param  array  $data
     * @return array|mixed
     *
     * @throws GuzzleException
     */
    public function httpPost(string $url, array $data = []): array
    {
        return $this->request($url, 'POST', ['form_params' => $data]);
    }

    /**
     * @param  string  $url
     * @param  array  $query
     * @return array
     *
     * @throws GuzzleException
     */
    public function httpGet(string $url, array $query = []): array
    {
        return $this->request($url, 'GET', ['query' => $query]);
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'timeout' => 30,
            'base_uri' => config('plugins.market.api_base'),
            'headers' => $this->getHeaders(),
            'verify' => false,
        ];
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        return  [
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer '.$this->getAuthorization(),
        ];
    }

    /**
     * @return string
     */
    public function getAuthorization(): string
    {
        if (App::runningInConsole()) {
            return Config::get('token', '');
        } else {
            return request()->header('token', '');
        }
    }

    /**
     * Get a HTTP client instance.
     *
     * @return Client
     */
    protected function client(): Client
    {
        return new Client($this->getConfig());
    }
}
