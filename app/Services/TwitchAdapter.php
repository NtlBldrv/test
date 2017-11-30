<?php

namespace App\Services;

use App\Game;
use App\Stream;
use App\StreamingService;
use Psr\Log\InvalidArgumentException;
use TwitchApi\TwitchApi;

class TwitchAdapter implements StreamingServiceAdapterInterface
{
    const STREAMS_LIMIT = 100;
    const TWITCH        = 'Twitch';
    const STREAM_TYPE   = 'live';

    /** @var TwitchApi */
    private $twitchClient;

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

        /** @var Stream[] $existingStreams */
        $existingStreams = Stream::query()->where('service_id', '=', $service->id)->get();

        $streams = [];
        foreach ($games as $game) {
            $streams[$game->name] = $this->twitchClient->getLiveStreams(
                null,
                $game->name,
                null,
                self::STREAM_TYPE,
                self::STREAMS_LIMIT
            );
        }

        if (!empty($streams)) {
            /** @var array $streams */
            foreach ($streams as $gameName => $apiResponse) {
                if (!isset($apiResponse['streams'])) {
                    throw new \OutOfRangeException('Check API response');
                }

                $gameStreams = $apiResponse['streams'];
                $game        = Game::query()->where('name', '=', $gameName)->get()->first();
                if (!empty($existingStreams)) {
                    $newStreams = $this->updateExistingStreams($existingStreams, $game, $gameStreams);
                }

                if (!empty($newStreams)) {
                    $this->inputStreamsIntoDB($game, $service, $newStreams);
                }
            }
        }
    }

    /**
     * @param Stream[] $existingStreams
     * @param Game     $game
     * @param array    $gameStreams
     *
     * @return array
     */
    private function updateExistingStreams($existingStreams, Game $game, array $gameStreams)
    {
        foreach ($existingStreams as $keyStream => $existingStream) {
            if ($game->id === $existingStream->game_id) {
                $existingStream->active = false;
                foreach ($gameStreams as $key => $stream) {
                    $this->checkStreamStructure($stream);
                    if ($stream['_id'] === $existingStream->stream_id) {
                        $existingStream->viewer_count = $stream['viewers'];
                        $existingStream->active       = true;
                        unset($gameStreams[$key]);
                        continue;
                    }
                }
            }
            $existingStream->save();
        }
        return $gameStreams;
    }

    private function inputStreamsIntoDB(Game $game, StreamingService $service, array $gameStreams)
    {
        foreach ($gameStreams as $key => $stream) {
            $this->checkStreamStructure($stream);
            $newStream               = new Stream();
            $newStream->channel_id   = $stream['channel']['_id'];
            $newStream->stream_id    = $stream['_id'];
            $newStream->game_id      = $game->id;
            $newStream->service_id   = $service->id;
            $newStream->viewer_count = $stream['viewers'];
            $newStream->active       = true;
            $newStream->save();
        }
    }

    private function checkStreamStructure(array $stream)
    {
        if (!isset($stream['channel']['_id'], $stream['_id'], $stream['viewers'])) {
            throw new \OutOfRangeException('Check API response');
        }
    }
}
