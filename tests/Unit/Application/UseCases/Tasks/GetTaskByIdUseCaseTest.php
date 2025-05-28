<?php

namespace Tests\Unit\Application\UseCases\Tasks;

use App\Application\UseCases\Tasks\GetTaskByIdUseCase;
use App\Domain\Tasks\Entities\Task;
use App\Domain\Tasks\Repositories\TaskRepositoryInterface;
use Mockery;
use Tests\TestCase;

class GetTaskByIdUseCaseTest extends TestCase
{
    private $taskRepositoryMock;
    private GetTaskByIdUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->taskRepositoryMock = Mockery::mock(TaskRepositoryInterface::class);
        $this->useCase = new GetTaskByIdUseCase($this->taskRepositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_returns_task_when_found()
    {
        $taskId = 1;
        $expectedTask = new Task(['id' => $taskId]);

        $this->taskRepositoryMock
            ->shouldReceive('findById')
            ->with($taskId)
            ->andReturn($expectedTask);

        $result = $this->useCase->execute($taskId);

        $this->assertSame($expectedTask, $result);
    }

    /** @test */
    public function it_returns_null_when_task_not_found()
    {
        $taskId = 999;

        $this->taskRepositoryMock
            ->shouldReceive('findById')
            ->with($taskId)
            ->andReturn(null);

        $result = $this->useCase->execute($taskId);

        $this->assertNull($result);
    }

    /** @test */
    public function it_handles_repository_exception()
    {
        $taskId = 1;

        $this->taskRepositoryMock
            ->shouldReceive('findById')
            ->andThrow(new \Exception('Database error'));

        $this->expectException(\Exception::class);

        $this->useCase->execute($taskId);
    }
}
