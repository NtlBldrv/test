<?php

namespace Tests\Feature;

use App\Game;
use App\Stream;
use App\StreamingService;
use App\User;
use App\ViewerCountHistory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\ClientRepository;
use Tests\TestCase;

class StreamsTest extends TestCase
{
    use DatabaseTransactions;

    /** @var User */
    private $user;
    /** @var string */
    private $baseUrl = '/';
    /** @var array */
    private $headers = [];
    /** @var Stream[]|Collection */
    private $streams;
    /** @var Game */
    private $game;
    /** @var StreamingService */
    private $service;
    /** @var ViewerCountHistory[]|Collection */
    private $viewerCountHistory;

    public function setUp()
    {
        parent::setUp();
        $clientRepository = new ClientRepository();
        $client           = $clientRepository->createPersonalAccessClient(null, 'Test Personal Access Client', $this->baseUrl);
        DB::table('oauth_personal_access_clients')->insert(
            [
                'client_id'  => $client->id,
                'created_at' => new \DateTime,
                'updated_at' => new \DateTime,
            ]
        );

        $this->user                     = factory(User::class)->create();
        $token                          = $this->user->createToken('TestToken')->accessToken;
        $this->headers['Authorization'] = 'Bearer ' . $token;
        $this->game                     = factory(Game::class)->create();
        $this->service                  = factory(StreamingService::class)->create();
        $this->streams                  = factory(Stream::class, 150)->create(
            [
                'game_id'    => $this->game->id,
                'service_id' => $this->service->id
            ]
        );

        $this->viewerCountHistory = factory(ViewerCountHistory::class, 100)->create(
            [
                'stream_id' => function () {
                    return $this->streams->toArray()[array_rand($this->streams->toArray())]['id'];
                }
            ]
        );
    }

    public function testCheckBasicMethod()
    {
        $this->withoutMiddleware();
        $response = $this->json('GET', '/api/streams/');

        $response->assertStatus(Response::HTTP_OK)
                 ->assertJsonStructure(
                     [
                         'data' =>
                             [
                                 [
                                     'id',
                                     'game'    => ['id', 'name'],
                                     'channel_id',
                                     'stream_id',
                                     'service' => ['id', 'title'],
                                     'viewer_count',
                                     'created_at',
                                     'updated_at'
                                 ]
                             ]
                     ]
                 );
    }

    public function testViewerCount()
    {
        $this->withoutMiddleware();
        $response = $this->json('GET', 'api/streams/viewer_count/');

        $response->assertStatus(Response::HTTP_OK)
                 ->assertJsonStructure(
                     [
                         'success',
                         'data' => ['total_viewer_count']
                     ]
                 );
    }

    public function testViewerCountByGame()
    {
        $this->withoutMiddleware();
        $response = $this->json('GET', 'api/streams/viewer_count/?game_ids=' . $this->game->id);

        $response->assertStatus(Response::HTTP_OK)
                 ->assertJsonStructure(
                     [
                         'success',
                         'data' => ['total_viewer_count', $this->game->name]
                     ]
                 );
    }

    public function testViewerCountHistory()
    {
        $this->withoutMiddleware();


        $response = $this->json('GET', 'api/streams/viewer_count_history/');

        $response->assertStatus(Response::HTTP_OK)
                 ->assertJsonStructure(
                     [
                         'success',
                         'viewer_count' => [
                             'current_page',
                             'data' =>
                                 [
                                     [
                                         'id',
                                         'viewer_count',
                                         'stream_id',
                                         'created_at',
                                         'updated_at',
                                     ]
                                 ]
                         ]
                     ]
                 );
    }

    public function testViewerCountHistoryByGame()
    {
        $this->withoutMiddleware();


        $response = $this->json('GET', 'api/streams/viewer_count_history/?game_ids=' . $this->game->id);

        $response->assertStatus(Response::HTTP_OK)
                 ->assertJsonStructure(
                     [
                         'success',
                         'viewer_count' => [
                             'current_page',
                             'data' =>
                                 [
                                     [
                                         'id',
                                         'viewer_count',
                                         'stream_id',
                                         'created_at',
                                         'updated_at',
                                         'game_id',
                                     ]
                                 ]
                         ]
                     ]
                 );
    }
}
