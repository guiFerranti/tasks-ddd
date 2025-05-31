<?php

namespace Tests\Unit\Controllers\Tasks;

use App\Application\DTOs\ListTasksDTO;
use App\Application\UseCases\Tasks\ListDeletedTasksUseCase;
use App\Domain\Tasks\Entities\Task;
use App\Domain\Users\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ListDeletedTasksTest extends TestCase
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

        $this->useCaseMock = Mockery::mock(ListDeletedTasksUseCase::class);
        $this->app->instance(ListDeletedTasksUseCase::class, $this->useCaseMock);

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
    public function test_admin_can_list_deleted_tasks()
    {
        $tasks = Task::factory()->count(2)->create(['deleted_at' => now()]);

        $this->useCaseMock->shouldReceive('execute')
            ->with(Mockery::type(ListTasksDTO::class))
            ->andReturn($tasks);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken
        ])->getJson('/api/tasks/deleted');

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    /** @test */
    public function test_non_admin_cannot_list_deleted_tasks()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->regularToken
        ])->getJson('/api/tasks/deleted');

        $response->assertStatus(403);
    }

    /** @test */
    public function test_it_filters_deleted_tasks_by_assigned_user()
    {
        $assignedUser = User::factory()->create();
        $payload = ['assigned_to' => $assignedUser->id];

        $this->useCaseMock->shouldReceive('execute')
            ->with(Mockery::on(function($dto) use ($assignedUser) {
                return $dto instanceof ListTasksDTO &&
                    $dto->assignedTo === $assignedUser->id;
            }))
            ->andReturn(collect());

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken
        ])->getJson('/api/tasks/deleted?' . http_build_query($payload));

        $response->assertStatus(200);
    }

    /** @test */
    public function test_it_validates_filter_parameters()
    {
        $invalidPayloads = [
            ['assigned_to' => 'not-an-integer'],
            ['status' => 'invalid-status'],
            ['created_after' => 'invalid-date']
        ];

        foreach ($invalidPayloads as $payload) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->adminToken
            ])->getJson('/api/tasks/deleted?' . http_build_query($payload));

            $response->assertStatus(422);
        }
    }

    /** @test */
    public function test_it_handles_invalid_arguments()
    {
        $this->useCaseMock->shouldReceive('execute')
            ->andThrow(new \InvalidArgumentException('Invalid filter'));

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken
        ])->getJson('/api/tasks/deleted');

        $response->assertStatus(400)
            ->assertJson(['error' => 'Invalid filter']);
    }

    /** @test */
    public function test_it_handles_server_errors()
    {
        $this->useCaseMock->shouldReceive('execute')
            ->andThrow(new \Exception());

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken
        ])->getJson('/api/tasks/deleted');

        $response->assertStatus(500)
            ->assertJson(['error' => 'Erro ao listar tarefas excluídas']);
    }
}
