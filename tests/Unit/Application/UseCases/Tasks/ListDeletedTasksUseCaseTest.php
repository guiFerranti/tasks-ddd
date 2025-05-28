<?php

namespace Tests\Unit\Application\UseCases\Tasks;

use App\Application\DTOs\ListTasksDTO;
use App\Application\UseCases\Tasks\ListDeletedTasksUseCase;
use App\Domain\Tasks\Repositories\TaskRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

class ListDeletedTasksUseCaseTest extends TestCase
{
    private $taskRepositoryMock;
    private ListDeletedTasksUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->taskRepositoryMock = Mockery::mock(TaskRepositoryInterface::class);
        $this->useCase = new ListDeletedTasksUseCase($this->taskRepositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_lists_deleted_tasks_with_filters()
    {
        $dto = new ListTasksDTO(
            assignedTo: 1,
            status: \App\Domain\Tasks\Enums\TaskStatus::PENDING,
            createdAfter: '2023-01-01'
        );

        $expectedResult = new LengthAwarePaginator([], 0, 15);

        $this->taskRepositoryMock
            ->shouldReceive('listDeletedTasks')
            ->with([
                'assignedTo' => 1,
                'status' => 'pending',
                'createdAfter' => '2023-01-01'
            ])
            ->andReturn($expectedResult);

        $result = $this->useCase->execute($dto);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    /** @test */
    public function it_lists_deleted_tasks_with_partial_filters()
    {
        $dto = new ListTasksDTO(
            assignedTo: null,
            status: null,
            createdAfter: '2023-01-01'
        );

        $expectedResult = new LengthAwarePaginator([], 0, 15);

        $this->taskRepositoryMock
            ->shouldReceive('listDeletedTasks')
            ->with([
                'assignedTo' => null,
                'status' => null,
                'createdAfter' => '2023-01-01'
            ])
            ->andReturn($expectedResult);

        $result = $this->useCase->execute($dto);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    /** @test */
    public function it_handles_repository_exception()
    {
        $dto = new ListTasksDTO(
            assignedTo: null,
            status: null,
            createdAfter: '2023-01-01'
        );

        $this->taskRepositoryMock
            ->shouldReceive('listDeletedTasks')
            ->andThrow(new \Exception('Database error'));

        $this->expectException(\Exception::class);

        $this->useCase->execute($dto);
    }
}
