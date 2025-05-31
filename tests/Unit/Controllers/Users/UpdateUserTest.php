<?php

namespace Tests\Unit\Controllers\Users;

use App\Application\DTOs\UpdateUserDTO;
use App\Application\UseCases\Users\UpdateUserUseCase;
use App\Domain\Users\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class UpdateUserTest extends TestCase
{
    use RefreshDatabase;

    private $useCaseMock;
    private $user;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useCaseMock = Mockery::mock(UpdateUserUseCase::class);
        $this->app->instance(UpdateUserUseCase::class, $this->useCaseMock);

        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_updates_user_successfully()
    {
        $userToUpdate = User::factory()->create();
        $updateData = [
            'name' => 'Novo Nome',
            'email' => 'novo@email.com'
        ];

        $updatedUser = clone $userToUpdate;
        $updatedUser->name = $updateData['name'];
        $updatedUser->email = $updateData['email'];

        $this->useCaseMock->shouldReceive('execute')
            ->with($userToUpdate->id, Mockery::type(UpdateUserDTO::class))
            ->andReturn($updatedUser);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/users/' . $userToUpdate->id, $updateData);

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
                'name' => 'Novo Nome',
                'email' => 'novo@email.com'
            ]);
    }

    public function test_it_returns_404_when_user_not_found()
    {
        $nonExistentId = 999;
        $updateData = ['name' => 'Novo Nome'];

        $this->useCaseMock->shouldReceive('execute')
            ->andThrow(new \Exception('Usuário não encontrado'));

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/users/' . $nonExistentId, $updateData);

        $response->assertStatus(400)
            ->assertJson(['error' => 'Usuário não encontrado']);
    }

    public function test_it_validates_update_data()
    {
        $invalidData = [
            ['email' => 'email-invalido'],
            ['cpf' => '123'],
            ['name' => str_repeat('a', 256)]
        ];

        foreach ($invalidData as $data) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->token
            ])->putJson('/api/users/1', $data);

            $response->assertStatus(422);
        }
    }

    public function test_it_requires_authentication()
    {
        $response = $this->putJson('/api/users/1', ['name' => 'Teste']);
        $response->assertStatus(401);
    }

    public function test_it_rejects_mismatched_ids()
    {
        $payload = [
            'name' => 'Novo Nome',
            'id' => 999
        ];

        $this->useCaseMock->shouldReceive('execute')
            ->andThrow(new \Exception('IDs não correspondem'));

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/users/1', $payload);

        $response->assertStatus(400)
            ->assertJson(['error' => 'IDs não correspondem']);
    }
}
