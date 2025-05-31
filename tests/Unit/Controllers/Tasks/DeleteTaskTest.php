<?php

namespace Tests\Unit\Controllers\Tasks;

use App\Application\UseCases\Tasks\DeleteTaskUseCase;
use App\Domain\Tasks\Entities\Task;
use App\Domain\Users\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class DeleteTaskTest extends TestCase
{
    use RefreshDatabase;

    private $useCaseMock;
    private $adminUser;
    private $regularUser;
    private $adminToken;
    private $regularToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useCaseMock = Mockery::mock(DeleteTaskUseCase::class);
        $this->app->instance(DeleteTaskUseCase::class, $this->useCaseMock);

        $this->adminUser = User::factory()->create(['role' => 'admin']);
        $this->regularUser = User::factory()->create(['role' => 'user']);

        $this->adminToken = JWTAuth::fromUser($this->adminUser);
        $this->regularToken = JWTAuth::fromUser($this->regularUser);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function test_admin_can_delete_task()
    {
        $task = Task::factory()->create();

        $this->useCaseMock->shouldReceive('execute')
            ->with(Mockery::type(User::class), Mockery::type(Task::class))
            ->once();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken
        ])->deleteJson('/api/tasks/' . $task->id);

        $response->assertStatus(204);
    }

    /** @test */
    public function test_non_admin_cannot_delete_task()
    {
        $task = Task::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->regularToken
        ])->deleteJson('/api/tasks/' . $task->id);

        $response->assertStatus(403)
            ->assertJson(['error' => 'Acesso negado: apenas administradores']);
    }

    /** @test */
    public function test_requires_authentication()
    {
        $task = Task::factory()->create();
        $response = $this->deleteJson('/api/tasks/' . $task->id);
        $response->assertStatus(401);
    }

    /** @test */
    public function test_handles_deletion_failure()
    {
        $task = Task::factory()->create();

        $this->useCaseMock->shouldReceive('execute')
            ->andThrow(new \Exception('Database error', 500));

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken
        ])->deleteJson('/api/tasks/' . $task->id);

        $response->assertStatus(500)
            ->assertJson(['error' => 'Database error']);
    }
}
