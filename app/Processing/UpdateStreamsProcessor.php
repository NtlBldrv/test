<?php

namespace App\Processing;

use App\StreamingService;
use App\StreamingServiceRegistry;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class UpdateStreamsProcessor
{
    /** @var StreamingServiceRegistry */
    private $registry;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(StreamingServiceRegistry $registry, LoggerInterface $logger = null)
    {
        $this->registry = $registry;
        $this->logger   = $logger ?: new NullLogger();
    }

    public function updateStreams(StreamingService $streamingService)
    {
        try {
            $service = $this->registry->getStreamingService($streamingService);
            $service->updateStreams();
        } catch (\Exception $exception) {
            $this->logger->warning(
                sprintf('Streams update failed for streaming service %s: %s', $streamingService->getTitle(), $exception->getMessage())
            );
        }
    }
}
