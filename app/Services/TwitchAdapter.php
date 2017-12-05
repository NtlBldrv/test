<?php

namespace App\Services;

use App\API\Exceptions\UnsuccessfulResponseException;
use App\API\TwitchApi;
use App\Game;
use App\Stream;
use App\StreamingService;
use Psr\Log\InvalidArgumentException;

class TwitchAdapter implements StreamingServiceAdapterInterface
{
    const TWITCH = 'Twitch';

    /** @var TwitchApi */
    private $twitchClient;
    /** @var string */
    private $cursor;

    public function __construct(TwitchApi $twitchClient)
    {
        $this->twitchClient = $twitchClient;
    }

    /** {@inheritdoc} */
    public function updateStreams()
    {
        $games   = Game::all();

        if (empty($games)) {
            throw new \Exception('No games were found for update');
        }

        $service = StreamingService::query()->where('title', '=', self::TWITCH)->get()->first();

        if (!$service) {
            throw new InvalidArgumentException(sprintf('No streaming service was found with title %s', self::TWITCH));
        }

        /** @var Stream[] $existingStreams */
        $existingStreams = Stream::query()->where('service_id', '=', $service->id)->get();

        $gameIds = [];
        foreach ($games as $game) {
            $gameIds[] = $game->twitch_game_id;
        }

        do {
            $streams = $this->getLiveStreams($gameIds);

            if (!empty($streams)) {
                if (!empty($existingStreams)) {
                    $newStreams = $this->updateExistingStreams($existingStreams, $streams);
                }

                if (!empty($newStreams)) {
                    $this->inputStreamsIntoDB($service, $newStreams);
                }
            }
        } while ($this->cursor !== null);
    }

    private function getLiveStreams(array $gameIds)
    {
        $response = $this->twitchClient->getLiveStreams($gameIds, $this->cursor);
        var_export($response);
        $this->cursor = $response->getCursor();

        return $response->getStreams();
    }

    /**
     * @param Stream[] $existingStreams
     * @param array    $gameStreams
     *
     * @return array
     */
    private function updateExistingStreams($existingStreams, array $gameStreams): array
    {
        foreach ($existingStreams as $existingStream) {
            $existingStream->live = false;
            foreach ($gameStreams as $key => $stream) {
                if ($this->getStreamId($stream) === 26902093072 ||
                    $existingStream->stream_id === 26902093072) {
                    var_export([$this->getStreamId($stream) === $existingStream->stream_id, $this->getStreamId($stream), $existingStream->stream_id]);
                    }
                if ($this->getStreamId($stream) === $existingStream->stream_id) {
                    $existingStream->viewer_count = $this->getViewerCount($stream);
                    $existingStream->live         = true;
                    unset($gameStreams[$key]);
                    continue;
                }
            }

            $existingStream->save();
        }

        return $gameStreams;
    }

    /**
     * @param StreamingService $service
     * @param array            $gameStreams
     */
    private function inputStreamsIntoDB(StreamingService $service, array $gameStreams)
    {
        foreach ($gameStreams as $key => $stream) {
            $game = Game::query()->where('twitch_game_id', '=', $this->getGameId($stream))->get()->first();
            $newStream               = new Stream();
            $newStream->channel_id   = $this->getChannelId($stream);
            $newStream->stream_id    = $this->getStreamId($stream);
            $newStream->game_id      = $game->id;
            $newStream->service_id   = $service->id;
            $newStream->viewer_count = $this->getViewerCount($stream);
            $newStream->live         = true;
            $newStream->save();
        }
    }
    public function getViewerCount(array $stream): int
    {
        if (!isset($stream['viewer_count'])) {
            throw new UnsuccessfulResponseException('Stream does not contain field \'viewer_count\'');
        }

        return (int)$stream['viewer_count'];
    }

    public function getChannelId(array $stream): int
    {
        if (!isset($stream['user_id'])) {
            throw new UnsuccessfulResponseException('Stream does not contain field \'user_id\'');
        }

        return (int)$stream['user_id'];
    }

    public function getStreamId(array $stream): int
    {
        if (!isset($stream['id'])) {
            throw new UnsuccessfulResponseException('Stream does not contain field \'id\'');
        }

        return (int)$stream['id'];
    }

    public function getGameId(array $stream): int
    {
        if (!isset($stream['game_id'])) {
            throw new UnsuccessfulResponseException('Stream does not contain field \'game_id\'');
        }

        return (int)$stream['game_id'];
    }
}
