<?php

namespace Tests\Unit\Controllers\Tasks;

use App\Application\DTOs\CreateTaskDTO;
use App\Application\UseCases\Tasks\CreateTaskUseCase;
use App\Domain\Tasks\Entities\Task;
use App\Domain\Users\Entities\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class CreateTaskTest extends TestCase
{
    use RefreshDatabase;

    private $useCaseMock;
    private $user;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useCaseMock = Mockery::mock(CreateTaskUseCase::class);
        $this->app->instance(CreateTaskUseCase::class, $this->useCaseMock);

        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function test_it_creates_task_successfully()
    {
        $assignedUser = User::factory()->create();
        $payload = [
            'title' => 'Tarefa importante',
            'description' => 'Descrição detalhada da tarefa',
            'status' => 'pending',
            'assigned_to' => $assignedUser->id
        ];

        $task = new Task($payload);
        $task->created_by = $this->user->id;

        $this->useCaseMock->shouldReceive('execute')
            ->with(Mockery::type(User::class), Mockery::type(CreateTaskDTO::class))
            ->andReturn($task);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/tasks', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'title' => 'Tarefa importante',
                'description' => 'Descrição detalhada da tarefa',
                'status' => 'pending',
                'assigned_to' => $assignedUser->id,
                'created_by' => $this->user->id
            ]);
    }

    /** @test */
    public function test_it_validates_required_fields()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/tasks', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'title',
                'description',
                'status',
                'assigned_to'
            ]);
    }

    /** @test */
    public function test_it_handles_user_not_found()
    {
        $payload = [
            'title' => 'Tarefa',
            'description' => 'Descrição',
            'status' => 'pending',
            'assigned_to' => 999 // ID inexistente
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/tasks', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Dados inválidos',
                'errors' => [
                    'assigned_to' => [
                        'O usuário atribuído não foi encontrado'
                    ]
                ]
            ]);
    }

    /** @test */
    public function test_it_requires_authentication()
    {
        $response = $this->postJson('/api/tasks', []);
        $response->assertStatus(401);
    }

    /** @test */
    public function test_it_validates_status_enum()
    {
        $payload = [
            'title' => 'Tarefa',
            'description' => 'Descrição',
            'status' => 'invalid_status',
            'assigned_to' => 1
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/tasks', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }
}
