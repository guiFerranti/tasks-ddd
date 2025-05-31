<?php

namespace Tests\Unit\Controllers\Tasks;

use App\Application\DTOs\ListTasksDTO;
use App\Application\UseCases\Tasks\ListTasksUseCase;
use App\Domain\Tasks\Entities\Task;
use App\Domain\Users\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ListTasksTest extends TestCase
{
    use RefreshDatabase;

    private $useCaseMock;
    private $user;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useCaseMock = Mockery::mock(ListTasksUseCase::class);
        $this->app->instance(ListTasksUseCase::class, $this->useCaseMock);

        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function test_it_lists_tasks_successfully()
    {
        $tasks = Task::factory()->count(3)->create();

        $this->useCaseMock->shouldReceive('execute')
            ->with(Mockery::type(ListTasksDTO::class))
            ->andReturn($tasks);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    /** @test */
    public function test_it_filters_by_assigned_user()
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
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/tasks?' . http_build_query($payload));

        $response->assertStatus(200);
    }

    /** @test */
    public function test_it_filters_by_status()
    {
        $payload = ['status' => 'completed'];

        $this->useCaseMock->shouldReceive('execute')
            ->with(Mockery::on(function($dto) {
                return $dto instanceof ListTasksDTO &&
                    $dto->status->value === 'completed';
            }))
            ->andReturn(collect());

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/tasks?' . http_build_query($payload));

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
                'Authorization' => 'Bearer ' . $this->token
            ])->getJson('/api/tasks?' . http_build_query($payload));

            $response->assertStatus(422);
        }
    }

    /** @test */
    public function test_it_requires_authentication()
    {
        $response = $this->getJson('/api/tasks');
        $response->assertStatus(401);
    }

    /** @test */
    public function test_it_handles_invalid_arguments()
    {
        $this->useCaseMock->shouldReceive('execute')
            ->andThrow(new \InvalidArgumentException('Invalid filter'));

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/tasks');

        $response->assertStatus(400)
            ->assertJson(['error' => 'Invalid filter']);
    }

    /** @test */
    public function test_it_handles_server_errors()
    {
        $this->useCaseMock->shouldReceive('execute')
            ->andThrow(new \Exception());

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/tasks');

        $response->assertStatus(500)
            ->assertJson(['error' => 'Erro interno']);
    }
}
