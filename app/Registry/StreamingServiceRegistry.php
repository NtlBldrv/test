<?php

namespace App\Registry;

use App\Services\StreamingServiceAdapterInterface;
use App\StreamingService;

class StreamingServiceRegistry
{
    /** @var StreamingServiceAdapterInterface[] */
    private $streamingServices;

    public function getAdapter(string $title): StreamingServiceAdapterInterface
    {
        if (!$this->hasAdapter($title)) {
            throw new \OutOfBoundsException(
                sprintf('No streaming service found with title "%s"', $title)
            );
        }

        return $this->streamingServices[$title];
    }

    public function hasAdapter(string $title): bool
    {
        return array_key_exists($title, $this->streamingServices);
    }

    public function addAdapter(string $title, StreamingServiceAdapterInterface $adapter)
    {
        $this->streamingServices[$title] = $adapter;
    }

    /**
     * @return StreamingServiceAdapterInterface[]
     */
    public function all()
    {
        return $this->streamingServices;
    }
}
