<?php

namespace App\API;

use App\API\Exceptions\InvalidLimitException;
use App\API\Exceptions\InvalidStreamTypeException;
use App\API\Exceptions\InvalidTypeException;

trait Streams
{
    /**
     * @param array|null  $gameIds
     * @param string|null $afterCursor
     * @param string|null $beforeCursor
     * @param array|null  $languages
     * @param string      $streamType
     * @param int         $limit
     * @param string|null $communityIds
     * @param array|null  $userIds
     * @param array|null  $userLogins
     *
     * @return Response
     *
     * @throws InvalidLimitException
     * @throws InvalidStreamTypeException
     * @throws InvalidTypeException
     */
    public function getLiveStreams(
        $gameIds = null,
        $afterCursor = null,
        $beforeCursor = null,
        $languages = null,
        $streamType = 'live',
        $limit = 100,
        $communityIds = null,
        $userIds = null,
        $userLogins = null)
    {
        if ($gameIds) {
            if (!$this->isArrayOfIdsValid($gameIds)) {
                throw new InvalidTypeException('gameIds', 'array', gettype($gameIds));
            }
//            $gameIds = implode(',', $gameIds);
        }

        if ($communityIds) {
            if (!$this->isArrayOfIdsValid($communityIds)) {
                throw new InvalidTypeException('communityIds', 'array', gettype($communityIds));
            }
            $communityIds = implode(',', $communityIds);
        }

        if ($userIds) {
            if (!$this->isArrayOfIdsValid($userIds)) {
                throw new InvalidTypeException('userIds', 'array', gettype($userIds));
            }
            $userIds = implode(',', $userIds);
        }

        if ($languages) {
            if (!$this->isArrayOfStringsValid($languages)) {
                throw new InvalidTypeException('languages', 'array of strings', gettype($languages));
            }
            $languages = implode(',', $languages);
        }

        if ($userLogins) {
            if (!$this->isArrayOfStringsValid($userLogins)) {
                throw new InvalidTypeException('userLogins', 'array of strings', gettype($userLogins));
            }
            $userLogins = implode(',', $userLogins);
        }

        if ($afterCursor && !is_string($afterCursor)) {
            throw new InvalidTypeException('afterCursor', 'string', gettype($afterCursor));
        }

        if ($beforeCursor && !is_string($beforeCursor)) {
            throw new InvalidTypeException('beforeCursor', 'string', gettype($beforeCursor));
        }

        if (!$this->isValidStreamType($streamType)) {
            throw new InvalidStreamTypeException();
        }

        if (!$this->isValidLimit($limit)) {
            throw new InvalidLimitException();
        }

        $params = [
            'game_id'      => $gameIds,
            'community_id' => $communityIds,
            'after'        => $afterCursor,
            'before'       => $beforeCursor,
            'language'     => $languages,
            'type'         => $streamType,
            'first'        => (int)$limit,
            'user_id'      => $userIds,
            'user_login'   => $userLogins,
        ];

        return $this->get('streams', $params);
    }

    private function isArrayOfIdsValid($ids): bool
    {
        if (!is_array($ids)) {
            return false;
        }

        foreach ($ids as $id) {
            if (!is_numeric($id)) {
                return false;
            }
        }

        return true;
    }

    private function isArrayOfStringsValid($array)
    {
        if (!is_array($array)) {
            return false;
        }

        foreach ($array as $element) {
            if (!is_string($element)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return true if the provided stream type is valid
     *
     * @param string $streamType
     *
     * @return bool
     */
    private function isValidStreamType(string $streamType): bool
    {
        return in_array(strtolower($streamType), self::VALID_STREAM_TYPES);
    }

    /**
     * Return true if the provided limit is valid
     *
     * @param string|int $limit
     *
     * @return bool
     */
    protected function isValidLimit($limit): bool
    {

        return is_numeric($limit) && $limit > 0 && $limit <= self::MAX_LIMIT;
    }
}
