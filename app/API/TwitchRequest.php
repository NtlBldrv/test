<?php

namespace App\API;

use GuzzleHttp;

class TwitchRequest
{
    const GET_METHOD = 'GET';
    /** @var string */
    protected $baseUri = 'https://api.twitch.tv/helix/';
    /** @var float */
    protected $timeout = 5.0;
    /** @var bool */
    protected $httpErrors = false;

    /**
     * Send the request
     *
     * @param string $method
     * @param string $endpoint
     * @param array  $params
     * @param bool   $accessToken
     *
     * @return Response
     */
    protected function sendRequest($method, $endpoint, $params = [], $accessToken = null)
    {
        $client       = $this->getNewHttpClient($method, $params, $accessToken);
        $response     = $client->request($method, $endpoint);
        $responseBody = $response->getBody()->getContents();

        return new Response(json_decode($responseBody, true));
    }

    /**
     * Send a GET request
     *
     * @param string $endpoint
     * @param array  $params
     * @param bool   $accessToken
     *
     * @return Response
     */
    protected function get($endpoint, $params = [], $accessToken = null)
    {
        return $this->sendRequest(self::GET_METHOD, $endpoint, $params, $accessToken);
    }

    /**
     * Get a new HTTP Client
     *
     * @param string $method
     * @param array  $params
     * @param string $accessToken
     *
     * @return GuzzleHttp\Client
     */
    protected function getNewHttpClient($method, $params, $accessToken = null)
    {
        $config = [
            'http_errors' => $this->getHttpErrors(),
            'base_uri'    => $this->baseUri,
            'timeout'     => $this->getTimeout(),
            'headers'     => [
                'Client-ID'  => $this->getClientId(),
            ],
        ];

        if ($accessToken) {
            $config['headers']['Authorization'] = sprintf('Bearer %s', $accessToken);
        }

        if (!empty($params)) {
            $config[($method == self::GET_METHOD) ? 'query' : 'json'] = $params;
        }

        var_export($config);
        return new GuzzleHttp\Client($config);
    }

    /**
     * Set timeout
     *
     * @param float $timeout
     */
    public function setTimeout($timeout)
    {
        $this->timeout = (float)$timeout;
    }

    /**
     * Get timeout
     *
     * @return float
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Set HTTP errors
     *
     * @param bool $httpErrors
     */
    public function setHttpErrors($httpErrors)
    {
        $this->httpErrors = (bool)$httpErrors;
    }

    /**
     * Get HTTP errors
     *
     * @return bool
     */
    public function getHttpErrors()
    {
        return $this->httpErrors;
    }
}
