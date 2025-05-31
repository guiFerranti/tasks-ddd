<?php

namespace Tests\Unit\Controllers\Users;

use App\Application\UseCases\Users\GetUserByIdUseCase;
use App\Domain\Users\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ShowUserTest extends TestCase
{
    use RefreshDatabase;

    private $useCaseMock;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useCaseMock = Mockery::mock(GetUserByIdUseCase::class);
        $this->app->instance(GetUserByIdUseCase::class, $this->useCaseMock);

        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_returns_user_successfully()
    {
        $testUser = User::factory()->create();

        $this->useCaseMock->shouldReceive('execute')
            ->with($testUser->id)
            ->andReturn($testUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/users/' . $testUser->id);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'cpf',
                    'role',
                    'created_at'
                ]
            ])
            ->assertJsonFragment([
                'id' => $testUser->id,
                'name' => $testUser->name,
                'email' => $testUser->email
            ]);
    }

    public function test_it_returns_404_when_user_not_found()
    {
        $nonExistentId = 999;

        $this->useCaseMock->shouldReceive('execute')
            ->with($nonExistentId)
            ->andReturn(null);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/users/' . $nonExistentId);

        $response->assertStatus(404)
            ->assertJson(['error' => 'Usuário não encontrado']);
    }

    public function test_it_requires_authentication()
    {
        $response = $this->getJson('/api/users/1');
        $response->assertStatus(401);
    }
}
