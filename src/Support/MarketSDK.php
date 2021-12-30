<?php
namespace Yxx\LaravelPlugin\Support;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

class MarketSDK
{
    /**
     * @param  array  $options
     * @return array
     * @throws GuzzleException
     */
    public function upload(array $options): array
    {
        return $this->request("/api/pluginmarket/plugins", 'POST', $options);
    }

    /**
     * @param  string  $url
     * @param  string  $method
     * @param  array  $options
     * @param  int  $tries
     * @return mixed
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

                return $this->request($url,$method, $options, $tries + 1);
            }

            throw $e;
        }

    }
    /**
     * Get a HTTP client instance.
     *
     * @return Client
     */
    protected function client(): Client
    {
        return new Client([
            'base_uri' => config('plugins.market.api_base'),
        ]);
    }
}