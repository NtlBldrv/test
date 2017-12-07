<?php

namespace App\Services;

use App\Game;
use App\Stream;
use App\StreamingService;
use App\ViewerCountHistory;
use Psr\Log\InvalidArgumentException;
use TwitchApi\TwitchApi;

class TwitchAdapter implements StreamingServiceAdapterInterface
{
    const STREAMS_LIMIT = 100;
    const TWITCH        = 'Twitch';
    const STREAM_TYPE   = 'live';

    /** @var TwitchApi */
    private $twitchClient;
    /** @var Stream[] */
    private $existingStreams;

    public function __construct(TwitchApi $twitchClient)
    {
        $this->twitchClient = $twitchClient;
    }

    /** {@inheritdoc} */
    public function updateStreams()
    {
        $games   = Game::all();
        $service = StreamingService::query()->where('title', '=', self::TWITCH)->get()->first();

        if (!$service) {
            throw new InvalidArgumentException(sprintf('No streaming service was found with title %s', self::TWITCH));
        }

        foreach ($games as $game) {
            $offset                = 0;
            $this->existingStreams = [];

            $query = Stream::query()->where('service_id', '=', $service->id)
                                    ->where('game_id', '=', $game->id);
            $query->update(
                [
                    'viewer_count' => 0,
                    'live'         => false,
                ]
            );

            /** @var Stream[] $existingStreams */
            $this->existingStreams = $query->lockForUpdate()->get();
            do {
                $response = $this->twitchClient->getLiveStreams(
                    null,
                    $game->name,
                    null,
                    self::STREAM_TYPE,
                    self::STREAMS_LIMIT,
                    $offset
                );

                if (!empty($response)) {
                    if (!isset($response['streams'], $response['_total'])) {
                        throw new \OutOfRangeException('Check API response');
                    }

                    $gameStreams = $response['streams'];
                    if (!empty($this->existingStreams)) {
                        $newStreams = $this->updateExistingStreams($game, $gameStreams);
                    }

                    if (!empty($newStreams)) {
                        $this->inputStreamsIntoDB($game, $service, $newStreams);
                    }
                }

                $total  = $response['_total'];
                $offset += self::STREAMS_LIMIT;
            } while ($offset <= $total);
        }
    }

    /**
     * @param Game     $game
     * @param array    $gameStreams
     *
     * @return array
     */
    private function updateExistingStreams(Game $game, array $gameStreams)
    {
        foreach ($this->existingStreams as $keyStream => $existingStream) {
            foreach ($gameStreams as $key => $stream) {
                $this->checkArrayStructure($stream);
                if ($stream['_id'] === $existingStream->stream_id) {
                    $existingStream->viewer_count = $stream['viewers'];
                    $existingStream->live         = true;
                    $existingStream->game()->associate($game);
                    $existingStream->save();
                    $viewerCountHistory               = new ViewerCountHistory();
                    $viewerCountHistory->viewer_count = $stream['viewers'];
                    $viewerCountHistory->stream()->associate($existingStream);
                    $viewerCountHistory->save();
                    unset($gameStreams[$key]);
                }
            }
        }

        return $gameStreams;
    }

    private function inputStreamsIntoDB(Game $game, StreamingService $service, array $gameStreams)
    {
        foreach ($gameStreams as $key => $stream) {
            $this->checkArrayStructure($stream);
            $newStream               = new Stream();
            $newStream->channel_id   = $stream['channel']['_id'];
            $newStream->stream_id    = $stream['_id'];
            $newStream->viewer_count = $stream['viewers'];
            $newStream->live         = true;
            $newStream->game()->associate($game);
            $newStream->service()->associate($service);
            $newStream->save();
            $this->existingStreams[] = $newStream;

            $viewerCountHistory               = new ViewerCountHistory();
            $viewerCountHistory->viewer_count = $stream['viewers'];
            $viewerCountHistory->stream()->associate($newStream);
            $viewerCountHistory->save();
        }
    }

    private function checkArrayStructure(array $stream)
    {
        if (!isset($stream['channel']['_id'], $stream['_id'], $stream['viewers'])) {
            throw new \OutOfRangeException('Check API response');
        }
    }
}
