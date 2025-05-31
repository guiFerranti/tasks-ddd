<?php

namespace Tests\Unit\Controllers\Tasks;

use App\Application\UseCases\Tasks\GetTaskByIdUseCase;
use App\Domain\Tasks\Entities\Task;
use App\Domain\Users\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ShowTaskTest extends TestCase
{
    use RefreshDatabase;

    private $useCaseMock;
    private $user;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useCaseMock = Mockery::mock(GetTaskByIdUseCase::class);
        $this->app->instance(GetTaskByIdUseCase::class, $this->useCaseMock);

        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function test_it_shows_task_successfully()
    {
        $task = Task::factory()->create();

        $this->useCaseMock->shouldReceive('execute')
            ->with($task->id)
            ->andReturn($task);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/tasks/' . $task->id);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'created_by',
                    'assigned_to',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    /** @test */
    public function test_it_returns_404_when_task_not_found()
    {
        $nonExistentId = 999;

        $this->useCaseMock->shouldReceive('execute')
            ->with($nonExistentId)
            ->andReturn(null);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/tasks/' . $nonExistentId);

        $response->assertStatus(404)
            ->assertJson(['error' => 'Tarefa não encontrada']);
    }

    /** @test */
    public function test_it_requires_authentication()
    {
        $task = Task::factory()->create();
        $response = $this->getJson('/api/tasks/' . $task->id);
        $response->assertStatus(401);
    }

    /** @test */
    public function test_it_handles_repository_errors()
    {
        $task = Task::factory()->create();

        $this->useCaseMock->shouldReceive('execute')
            ->andThrow(new \Exception('Database error'));

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/tasks/' . $task->id);

        $response->assertStatus(500);
    }
}
