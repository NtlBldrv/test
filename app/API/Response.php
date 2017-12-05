<?php

namespace App\API;

use App\API\Exceptions\UnsuccessfulResponseException;

class Response
{
    protected $response;

    public function __construct(array $response)
    {
        $this->response = $response;
    }

    public function getStreams(): array
    {
        if (!isset($this->response['data'])) {
            throw new UnsuccessfulResponseException('Response does not contain field \'data\'');
        }

        return $this->response['data'];
    }

    /**
     * @return string|null
     * @throws UnsuccessfulResponseException
     */
    public function getCursor()
    {
        if (!isset($this->response['pagination'])) {
            throw new UnsuccessfulResponseException('Response does not contain field \'cursor\'');
        }

        if (!isset($this->response['pagination']['cursor'])) {
            return null;
        }

        return $this->response['pagination']['cursor'];
    }
}
