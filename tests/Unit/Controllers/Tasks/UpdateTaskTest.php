<?php

namespace Tests\Unit\Controllers\Tasks;

use App\Application\DTOs\UpdateTaskDTO;
use App\Application\UseCases\Tasks\UpdateTaskUseCase;
use App\Domain\Tasks\Entities\Task;
use App\Domain\Tasks\Enums\TaskStatus;
use App\Domain\Users\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class UpdateTaskTest extends TestCase
{
    use RefreshDatabase;

    private $useCaseMock;
    private $user;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useCaseMock = Mockery::mock(UpdateTaskUseCase::class);
        $this->app->instance(UpdateTaskUseCase::class, $this->useCaseMock);

        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function test_it_updates_task_successfully()
    {
        $task = Task::factory()->create();
        $payload = [
            'title' => 'Novo título',
            'status' => 'completed'
        ];

        $updatedTask = clone $task;
        $updatedTask->title = $payload['title'];
        $updatedTask->status = TaskStatus::COMPLETED;

        $this->useCaseMock->shouldReceive('execute')
            ->with(Mockery::type(Task::class), Mockery::type(UpdateTaskDTO::class))
            ->andReturn($updatedTask);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/tasks/' . $task->id, $payload);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'id' => $task->id,
                    'title' => 'Novo título',
                    'status' => 'completed'
                ]
            ]);
    }

    /** @test */
    public function test_it_validates_update_data()
    {
        $task = Task::factory()->create();
        $invalidPayloads = [
            ['title' => str_repeat('a', 256)],
            ['description' => str_repeat('a', 1001)],
            ['status' => 'invalid_status']
        ];

        foreach ($invalidPayloads as $payload) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->token
            ])->putJson('/api/tasks/' . $task->id, $payload);

            $response->assertStatus(422);
        }
    }

    /** @test */
    public function test_it_handles_partial_updates()
    {
        $task = Task::factory()->create(['title' => 'Título original']);
        $payload = ['description' => 'Nova descrição'];

        $updatedTask = clone $task;
        $updatedTask->description = $payload['description'];

        $this->useCaseMock->shouldReceive('execute')
            ->andReturn($updatedTask);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/tasks/' . $task->id, $payload);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'title' => 'Título original',
                    'description' => 'Nova descrição'
                ]
            ]);
    }

    /** @test */
    public function test_it_requires_authentication()
    {
        $task = Task::factory()->create();
        $response = $this->putJson('/api/tasks/' . $task->id, []);
        $response->assertStatus(401);
    }

    /** @test */
    public function test_it_handles_update_failure()
    {
        $task = Task::factory()->create();
        $payload = ['title' => 'Novo título'];

        $this->useCaseMock->shouldReceive('execute')
            ->andThrow(new \Exception('Database error'));

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/tasks/' . $task->id, $payload);

        $response->assertStatus(400)
            ->assertJson(['error' => 'Database error']);
    }
}
