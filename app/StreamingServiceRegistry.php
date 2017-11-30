<?php

namespace App;

class StreamingServiceRegistry
{
    /** @var StreamingServiceAdapterInterface[] */
    private $streamingServices;

    public function __construct(StreamingServiceAdapterInterface $streamingServices)
    {
        $this->streamingServices = $streamingServices;
    }

    public function getStreamingService(StreamingService $service)
    {
        if (!$this->hasStreamingService($service)) {
            throw new \OutOfBoundsException(
                sprintf('No streaming service found with title "%s"', $service->getTitle())
            );
        }

        return $this->streamingServices[$service->getTitle()];
    }

    public function hasStreamingService(StreamingService $service)
    {
        return array_key_exists($service->getTitle(), $this->streamingServices);
    }

    public function addStreamingService(StreamingService $service)
    {
        $this->streamingServices[$service->getTitle()] = $service;
    }

    public function all()
    {
        return $this->streamingServices;
    }
}
