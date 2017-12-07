## README

Every hour (another period of time can be specified) the console command update:stream is run. The command updates the streams for games configured in DB

To access the API user should be registered and user's IP should be in the list of IP "VALID_IPS" or "VALID_IP_RANGE" given in .env file.

```php
POST api/register/ - returns an access token for futher use of the API
```
the body for the request should include a unique email, name and password. For example:
```json
{
    "name": "Abcd",
    "email": "abcd@abc.com",
    "password": "1234567"
}
```

Example of the successful response:
```json
{
    "success": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjMxOWZlMDQwMWI2MzZhMDc0ODFkODk3",
        "name": "Abcd"
    }
}
```

Header "Authorization: Bearer access token" should be passed for the usage of the following urls.

Available routes and filters: 
```php
GET /api/streams/ - returns all streams that exist in the DB
```

Available filters:

* game_ids - you can specify several game ids (the query should look like game_ids=1,2,3);
* live - constrains the status of the streams that would be returned (the query live=1 would return only live streams, 
the query live=0 would return the offline streams)
* datetimefrom - only streams that were updated from the specified date to today would be returned (works like equals or greater)
* datetimeto - only streams that were updated prior to the specified date would be returned (works like equals or less)

```php
GET /api/viewer_count/ - returns the number of viewers for all streams that exist in the DB
```

Available filters:

* game_ids - you can specify several game ids (the query should look like game_ids=1,2,3), if this filter is used not only the total
viewers count would be returned, but also viewers count by game;
* live - constrains the status of the streams that would be returned (the query live=1 would return only live streams, 
the query live=0 would return only offline streams)
* datetimefrom - only streams that were updated from the specified date to today would be returned (works like equals or greater)
* datetimeto - only streams that were updated prior to the specified date would be returned (works like equals or less)

```php
GET /api/viewer_count_history/ - returns the number of viewers for a period of time for all streams 
that exist in the DB, if sum_up filter is not passed then a list of all streams would be returned
```

Available filters:

* game_ids - you can specify several game ids (the query should look like game_ids=1,2,3);
* datetimefrom - only streams that were updated from the specified date to today would be returned (works like equals or greater)
* datetimeto - only streams that were updated prior to the specified date would be returned (works like equals or less)
* sum_up=1 - would return a total of viewers for all time or a specified period of time, if the filter game_ids is given then it would also
return viewer count by game
