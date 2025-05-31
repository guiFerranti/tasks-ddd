<?php

namespace Tests\Unit\Application\UseCases\Tasks;

use App\Application\DTOs\CreateTaskDTO;
use App\Application\UseCases\Tasks\CreateTaskUseCase;
use App\Domain\Tasks\Entities\Task;
use App\Domain\Tasks\Repositories\TaskRepositoryInterface;
use App\Domain\Users\Entities\User;
use Mockery;
use Tests\TestCase;

class CreateTaskUseCaseTest extends TestCase
{
    private $taskRepositoryMock;
    private CreateTaskUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->taskRepositoryMock = Mockery::mock(TaskRepositoryInterface::class);
        $this->useCase = new CreateTaskUseCase($this->taskRepositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    ///** @test */
    //public function it_creates_task_successfully()
    //{
    //    $userMock = Mockery::mock(User::class);
    //    $userMock->shouldReceive('findOrFail')
    //        ->with(2)
    //        ->andReturnSelf();
    //
    //    $userMock->id = 2;
    //
    //    $creatorMock = Mockery::mock(User::class);
    //    $creatorMock->id = 1;
    //
    //    $dto = CreateTaskDTO::fromValidatedData([
    //        'title' => 'Tarefa Teste',
    //        'description' => 'Descrição da tarefa',
    //        'status' => 'pending',
    //        'assigned_to' => $userMock->id
    //    ]);
    //
    //    $expectedTask = new Task([
    //        'title' => 'Tarefa Teste',
    //        'description' => 'Descrição da tarefa',
    //        'status' => 'pending',
    //        'created_by' => $creatorMock->id,
    //        'assigned_to' => $userMock->id
    //    ]);
    //
    //    $this->taskRepositoryMock
    //        ->shouldReceive('create')
    //        ->with([
    //            'title' => 'Tarefa Teste',
    //            'description' => 'Descrição da tarefa',
    //            'status' => 'pending',
    //            'created_by' => $creatorMock->id,
    //            'assigned_to' => $userMock->id
    //        ])
    //        ->andReturn($expectedTask);
    //
    //    $result = $this->useCase->execute($creatorMock, $dto);
    //
    //    $this->assertInstanceOf(Task::class, $result);
    //    $this->assertEquals('Tarefa Teste', $result->title);
    //}

    /** @test */
    public function it_throws_exception_when_repository_fails()
    {
        $creator = new User(['id' => 1]);
        $assignedUser = new User(['id' => 2]);
        $dto = new CreateTaskDTO(
            title: 'Tarefa Teste',
            description: 'Descrição da tarefa',
            status: \App\Domain\Tasks\Enums\TaskStatus::PENDING,
            assignedTo: $assignedUser
        );

        $this->taskRepositoryMock
            ->shouldReceive('create')
            ->andThrow(new \Exception('Database error'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database error');

        $this->useCase->execute($creator, $dto);
    }
}
