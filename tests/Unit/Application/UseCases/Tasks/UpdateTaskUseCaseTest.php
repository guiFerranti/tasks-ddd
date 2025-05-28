<?php

namespace Tests\Unit\Application\UseCases\Tasks;

use App\Application\DTOs\UpdateTaskDTO;
use App\Application\UseCases\Tasks\UpdateTaskUseCase;
use App\Domain\Tasks\Entities\Task;
use App\Domain\Tasks\Repositories\TaskRepositoryInterface;
use Mockery;
use Tests\TestCase;

class UpdateTaskUseCaseTest extends TestCase
{
    private $taskRepositoryMock;
    private UpdateTaskUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->taskRepositoryMock = Mockery::mock(TaskRepositoryInterface::class);
        $this->useCase = new UpdateTaskUseCase($this->taskRepositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_updates_task_with_all_fields()
    {
        $task = new Task();
        $dto = new UpdateTaskDTO(
            title: 'Novo Título',
            description: 'Nova Descrição',
            status: \App\Domain\Tasks\Enums\TaskStatus::COMPLETED
        );

        $this->taskRepositoryMock
            ->shouldReceive('update')
            ->with($task, [
                'title' => 'Novo Título',
                'description' => 'Nova Descrição',
                'status' => 'completed'
            ])
            ->andReturn($task);

        $result = $this->useCase->execute($task, $dto);

        $this->assertInstanceOf(Task::class, $result);
    }

    /** @test */
    public function it_updates_task_partially()
    {
        $task = new Task();
        $dto = new UpdateTaskDTO(
            title: 'Novo Título',
            description: null,
            status: null
        );

        $this->taskRepositoryMock
            ->shouldReceive('update')
            ->with($task, [
                'title' => 'Novo Título'
            ])
            ->andReturn($task);

        $result = $this->useCase->execute($task, $dto);

        $this->assertInstanceOf(Task::class, $result);
    }

    /** @test */
    public function it_handles_repository_exception()
    {
        $task = new Task();
        $dto = new UpdateTaskDTO(
            title: 'Novo Título',
            description: null,
            status: null
        );

        $this->taskRepositoryMock
            ->shouldReceive('update')
            ->andThrow(new \Exception('Database error'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database error');

        $this->useCase->execute($task, $dto);
    }
}
