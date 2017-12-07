<?php

namespace App\Http\Controllers;

use App\Game;
use App\Http\Resources\Stream as StreamResource;
use App\Stream;
use App\ViewerCountHistory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Psr\Log\InvalidArgumentException;
use TwitchApi\Exceptions\InvalidIdentifierException;
use TwitchApi\Exceptions\InvalidTypeException;

class StreamController extends Controller
{
    const PER_PAGE         = 25;
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
            $query   = Stream::query();
            $this->fillTimeConditions($request, $query);
            $this->fillLiveCondition($request, $query);
            $this->fillGameCondition($request, $query);

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
            $query = Stream::query();
            $this->fillTimeConditions($request, $query);
            $this->fillLiveCondition($request, $query);
            $games       = $this->fillGameCondition($request, $query, true);
            $streams     = $query->get();
            $viewerCount = 0;

            if (!empty($games)) {
                $data = $this->getViewerCountByGame($games, $streams);
            }

            foreach ($streams as $stream) {
                $viewerCount += $stream->viewer_count;
            }

            $data['total_viewer_count'] = $viewerCount;

            return new JsonResponse(['success' => true, 'data' => $data]);
        } catch (\Exception $exception) {
            return new JsonResponse(['success' => false, 'error' => $exception->getMessage()]);
        }
    }

    public function viewerCountHistory(Request $request)
    {
        try {
            $query            = ViewerCountHistory::query();
            $updateColumnName = 'updated_at';
            if ($gameIds = $request->query('game_ids')) {
                if (!is_string($gameIds)) {
                    throw new InvalidTypeException('gameIds', 'string', gettype($gameIds));
                }

                $gameIds = explode(',', $gameIds);
                $games   = [];
                foreach ($gameIds as $gameId) {
                    if (!is_numeric($gameId)) {
                        throw new InvalidIdentifierException('gameId');
                    }

                    $game = Game::query()->find((int)$gameId);
                    if (!$game) {
                        throw new InvalidArgumentException(sprintf('No game was found with id %s', $gameId));
                    }

                    if ($request->query('sum_up')) {
                        $games[] = $game;
                    }
                }
                $query->join('streams', 'viewer_count_history.stream_id', '=', 'streams.id')
                      ->whereIn('streams.game_id', $gameIds)
                      ->select('viewer_count_history.*', 'streams.game_id');
                $updateColumnName = 'viewer_count_history.updated_at';
            }

            $this->fillTimeConditions($request, $query, $updateColumnName);
            $sumQuery = clone $query;

            $viewerCount = $query->paginate(self::PER_PAGE);

            if ($sumUpParam = $request->query('sum_up')) {
                $viewerCount = [];
                $countAll    = 0;
                if (!is_numeric($sumUpParam)) {
                    throw new InvalidTypeException('sum_up', 'integer', gettype($sumUpParam));
                }

                $allHistoryItems = $sumQuery->get();
                foreach ($allHistoryItems as $key => $item) {
                    $countAll                   += $item->viewer_count;
                    $viewerCount['total_count'] = $countAll;
                }

                if (!empty($games)) {
                    foreach ($games as $game) {
                        $count = 0;
                        foreach ($allHistoryItems as $key => $item) {
                            if ($game->id === $item->game_id) {
                                $count += $item->viewer_count;
                                unset($allHistoryItems[$key]);
                                continue;
                            }
                        }
                        $viewerCount[$game->name] = $count;
                    }
                }
            }

            return new JsonResponse(['success' => true, 'viewer_count' => $viewerCount]);
        } catch (\Exception $exception) {
            return new JsonResponse(['success' => false, 'error' => $exception->getMessage()]);
        }
    }

    /**
     * @param Game[]     $games
     * @param Collection $streams
     *
     * @return array
     */
    private function getViewerCountByGame($games, $streams)
    {
        $data = [];
        foreach ($games as $game) {
            $viewerCountByGame = 0;
            $streamsByGame     = $streams->where('game_id', '=', $game->id)->all();

            foreach ($streamsByGame as $streamByGame) {
                $viewerCountByGame += $streamByGame->viewer_count;
            }

            $data[$game->name] = $viewerCountByGame;
        }

        return $data;
    }

    /**
     * @param Request $request
     * @param Builder $query
     *
     * @throws InvalidIdentifierException
     * @throws InvalidTypeException
     */
    private function fillLiveCondition(Request $request, Builder $query)
    {
        if ($activeParam = $request->query('live')) {
            if (!is_string($activeParam)) {
                throw new InvalidTypeException('live', 'string', gettype($activeParam));
            }

            $query->where('live', $activeParam);
        }
    }

    private function fillGameCondition(Request $request, Builder $query, $saveFoundGames = true)
    {
        $games = [];
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
                if ($saveFoundGames) {
                    $games[] = $game;
                }
            }
            $query->whereIn('game_id', $gameIds);
        }

        return $games;
    }

    private function fillTimeConditions(Request $request, Builder $query, $columnName = 'updated_at')
    {
        if ($datetimeFromParam = $request->query('datetimefrom')) {
            if (!is_string($datetimeFromParam)) {
                throw new InvalidTypeException('datetimeFrom', 'string', gettype($datetimeFromParam));
            }

            $datetimeFrom = (new \DateTime($datetimeFromParam))->format(self::DATE_TIME_FORMAT);
            $query->whereDate($columnName, '>=', $datetimeFrom);
        }

        if ($datetimeToParam = $request->query('datetimeto')) {
            if (!is_string($datetimeToParam)) {
                throw new InvalidTypeException('datetimeTo', 'string', gettype($datetimeToParam));
            }

            $datetimeTo = (new \DateTime($datetimeToParam))->format(self::DATE_TIME_FORMAT);
            $query->whereDate($columnName, '<=', $datetimeTo);
        }
    }
}
