<?php

namespace App\Console\Commands;

use App\Registry\StreamingServiceRegistry;
use App\StreamingService;
use Illuminate\Console\Command;

class UpdateStreams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:stream {serviceId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates all live streams';

    /** @var StreamingServiceRegistry */
    private $registry;

    /**
     * Create a new command instance.
     *
     * @param StreamingServiceRegistry $registry
     */
    public function __construct(StreamingServiceRegistry $registry)
    {
        parent::__construct();

        $this->registry = $registry;
    }

    public function handle()
    {
        if ($serviceId = $this->argument('serviceId')) {
            $streamingService = StreamingService::query()->where('id', '=', $serviceId)->get()->first();
            $service = $this->registry->getAdapter($streamingService->title);
            $service->updateStreams();
            $this->info(sprintf('Stream service %s have been updated', $streamingService->title));
        }

        $services = $this->registry->all();

        foreach ($services as $service) {
            try {
                $service->updateStreams();
                $this->info('All streams have been updated');
            } catch (\Exception $exception) {
                $this->error(sprintf('Error: %s, trace: %s', $exception->getMessage(), $exception->getTraceAsString()));
            }
        }
    }
}
