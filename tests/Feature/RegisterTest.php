<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\ClientRepository;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use DatabaseTransactions;

    /** @var string */
    private $baseUrl = '/';

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
    }

    public function testRegister()
    {
        $user = factory(User::class)->make();
        $this->withoutMiddleware();
        $response = $this->json('POST', '/api/register/', ['name' => $user->name, 'email' => $user->email, 'password' => $user->password]);

        $response->assertStatus(Response::HTTP_OK)
                 ->assertJsonStructure(['success' => ['token', 'name']]);
    }
}
