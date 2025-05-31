<?php

namespace Tests\Unit\Controllers\Users\Admin;

use App\Application\UseCases\Users\DeleteUserUseCase;
use App\Domain\Users\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class DeleteUserTest extends TestCase
{
    use RefreshDatabase;

    private $useCaseMock;
    private $adminUser;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useCaseMock = Mockery::mock(DeleteUserUseCase::class);
        $this->app->instance(DeleteUserUseCase::class, $this->useCaseMock);

        $this->adminUser = User::factory()->create(['role' => 'admin']);
        $this->token = JWTAuth::fromUser($this->adminUser);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_admin_can_delete_user()
    {
        $userToDelete = User::factory()->create();

        $this->useCaseMock->shouldReceive('execute')
            ->with(Mockery::type(User::class), Mockery::type(User::class))
            ->once();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson('/api/users/' . $userToDelete->id);

        $response->assertStatus(204);
    }

    public function test_returns_403_when_non_admin_tries_to_delete()
    {
        $regularUser = User::factory()->create(['role' => 'user']);
        $token = JWTAuth::fromUser($regularUser);
        $userToDelete = User::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->deleteJson('/api/users/' . $userToDelete->id);

        $response->assertStatus(403)
            ->assertJson(['error' => 'Acesso negado: apenas administradores']);
    }

    public function test_returns_404_when_user_not_found()
    {
        $nonExistentId = 999;

        $this->useCaseMock->shouldReceive('execute')
            ->andThrow(new \Exception('Usuário não encontrado'));

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson('/api/users/' . $nonExistentId);

        $response->assertStatus(404);
    }

    public function test_requires_authentication()
    {
        $response = $this->deleteJson('/api/users/1');
        $response->assertStatus(401);
    }
}
