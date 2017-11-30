<?php

namespace App\Http\Controllers;

use App\Game;
use App\Http\Resources\Stream as StreamResource;
use App\Stream;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Psr\Log\InvalidArgumentException;
use TwitchApi\Exceptions\InvalidIdentifierException;
use TwitchApi\Exceptions\InvalidTypeException;

class StreamController extends Controller
{
    const PER_PAGE     = 25;
    const ACTIVE_TRUE  = 1;
    const ACTIVE_FALSE = 0;
    const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @param Request $request
     *
     * @return JsonResponse|AnonymousResourceCollection
     */
    public function allStreams(Request $request)
    {
        try {
            $streams = null;
            $query   = $this->fillQuery($request, Stream::query());

            return StreamResource::collection($query->paginate(self::PER_PAGE));
        } catch (\Exception $exception) {
            return new JsonResponse(['success' => false, 'error' => $exception->getMessage()]);
        }
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     */
    public function viewerCount(Request $request)
    {
        try {
            $streams     = $this->fillQuery($request, Stream::query())->get();
            $viewerCount = 0;
            foreach ($streams as $stream) {
                $viewerCount += $stream->viewer_count;
            }

            return new JsonResponse(['success' => true, 'viewer_count' => $viewerCount]);
        } catch (\Exception $exception) {
            return new JsonResponse(['success' => false, 'error' => $exception->getMessage()]);
        }
    }

    /**
     * @param Request $request
     * @param Builder $query
     *
     * @return Builder
     *
     * @throws InvalidIdentifierException
     * @throws InvalidTypeException
     */
    private function fillQuery(Request $request, Builder $query)
    {
        if ($gameIds = $request->query('game_ids')) {
            if (!is_string($gameIds)) {
                throw new InvalidTypeException('gameIds', 'string', gettype($gameIds));
            }

            $gameIds = explode(',', $gameIds);
            foreach ($gameIds as $gameId) {
                if (!is_numeric($gameId)) {
                    throw new InvalidIdentifierException('gameId');
                }

                $game = Game::query()->find($gameId);
                if (!$game) {
                    throw new InvalidArgumentException(sprintf('No game was found with id %s', $gameId));
                }
            }

            $query->whereIn('game_id', $gameIds);
        }

        if ($activeParam = $request->query('active')) {
            if (!is_string($activeParam)) {
                throw new InvalidTypeException('active', 'string', gettype($activeParam));
            }

            $query->where('active', $activeParam);
        }

        if ($datetimeFromParam = $request->query('datetimefrom')) {
            if (!is_string($datetimeFromParam)) {
                throw new InvalidTypeException('datetimeFrom', 'string', gettype($datetimeFromParam));
            }

            $datetimeFrom = (new \DateTime($datetimeFromParam))->format(self::DATE_TIME_FORMAT);
            $query->whereDate('updated_at', '>=', $datetimeFrom);
        }

        if ($datetimeToParam = $request->query('datetimeto')) {
            if (!is_string($datetimeToParam)) {
                throw new InvalidTypeException('datetimeTo', 'string', gettype($datetimeToParam));
            }

            $datetimeTo = (new \DateTime($datetimeToParam))->format(self::DATE_TIME_FORMAT);
            $query->whereDate('updated_at', '<=', $datetimeTo);
        }

        return $query;
    }
}
