<?php

namespace Tests\Unit\Controllers\Users\Admin;

use App\Application\UseCases\Users\ListAllUsersUseCase;
use App\Domain\Users\Entities\User;
use App\Domain\Users\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ListUsersTest extends TestCase
{
    use RefreshDatabase;

    private $useCaseMock;
    private $adminUser;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useCaseMock = Mockery::mock(ListAllUsersUseCase::class);
        $this->app->instance(ListAllUsersUseCase::class, $this->useCaseMock);

        $this->adminUser = User::factory()->create(['role' => UserRole::ADMIN->value]);
        $this->token = JWTAuth::fromUser($this->adminUser);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_lists_users_for_admin()
    {
        $users = User::factory()->count(3)->create();
        $paginator = new LengthAwarePaginator($users, 3, 15);

        $this->useCaseMock->shouldReceive('execute')
            ->with(Mockery::type(User::class))
            ->andReturn($paginator);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email', 'cpf', 'role', 'created_at']
                ],
                'meta' => ['current_page', 'per_page', 'total']
            ]);
    }

    public function test_it_denies_access_for_non_admins()
    {
        $regularUser = User::factory()->create(['role' => UserRole::USER->value]);
        $token = JWTAuth::fromUser($regularUser);

        $this->useCaseMock->shouldReceive('execute')
            ->andThrow(new \Exception('Acesso negado: apenas administradores', 403));

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/users');

        $response->assertStatus(403)
            ->assertJson(['error' => 'Acesso negado: apenas administradores']);
    }

    public function test_it_requires_authentication()
    {
        $response = $this->getJson('/api/users');
        $response->assertStatus(401);
    }
}
