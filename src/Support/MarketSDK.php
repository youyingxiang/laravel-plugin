<?php

namespace Yxx\LaravelPlugin\Support;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\UploadedFile;
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
     * @param  string  $status
     * @return array
     *
     * @throws GuzzleException
     */
    public function getUserPlugins(string $status): array
    {
        return $this->httpGet("/api/pluginmarket/user/plugins/{$status}");
    }

    /**
     * @param  int  $versionId
     * @return array
     * @throws GuzzleException
     */
    public function getPluginVersion(int $versionId): array
    {
        return $this->httpGet("/api/pluginmarket/pluginversions/{$versionId}");
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
     * @param  int  $versionId
     * @param  bool  $change_status
     * @param  float|null  $price
     * @param  string|null  $version
     * @param  string|null  $description
     * @param  string|null  $logo
     * @return array
     * @throws GuzzleException
     */
    public function updatePluginVersions(
        int $versionId,
        bool $change_status,
        ?float $price,
        ?string $version,
        ?string $description,
        ?string $logo
    ): array
    {
        return $this->httpPutJson("/api/pluginmarket/pluginversions/{$versionId}", compact(
            'change_status',
            'price',
             'version',
            'description',
            'logo'
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
     * @param  UploadedFile  $file
     * @return array
     * @throws GuzzleException
     */
    public function uploadImage(UploadedFile $file): array
    {
        return $this->httpUpload('/api/pluginmarket/upload/image', ['file' => $file]);
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
     * @param  array  $query
     * @return array
     * @throws GuzzleException
     */
    public function httpPutJson(string $url, array $data = [], array $query = []): array
    {
        return $this->request($url, 'PUT', ['query' => $query, 'json' => $data]);
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
     * @param  array  $files
     * @param  array  $form
     * @param  array  $query
     * @return array|mixed
     * @throws GuzzleException
     */
    public function httpUpload(string $url, array $files = [], array $form = [], array $query = []): array
    {
        $multipart = [];

        foreach ($files as $name => $file) {
            if ($file instanceof UploadedFile) {
                /** @var UploadedFile $file */
                $multipart[] = [
                    'name' => $name,
                    'filename' => $file->getClientOriginalName(),
                    'contents' => $file->getContent(),
                    'headers' => ['Content-Type' => $file->getClientMimeType()],
                ];
            }
        }

        foreach ($form as $name => $contents) {
            $headers = ['Content-Type' => 'application/x-www-form-urlencoded'];
            $multipart[] = compact('name', 'contents', 'headers');
        }

        return $this->request($url, 'POST', ['query' => $query, 'multipart' => $multipart]);
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
