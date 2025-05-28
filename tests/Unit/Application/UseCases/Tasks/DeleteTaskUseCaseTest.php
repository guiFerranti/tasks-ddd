<?php

namespace Tests\Unit\Application\UseCases\Tasks;

use App\Application\UseCases\Tasks\DeleteTaskUseCase;
use App\Domain\Tasks\Entities\Task;
use App\Domain\Tasks\Repositories\TaskRepositoryInterface;
use App\Domain\Users\Entities\User;
use App\Domain\Users\Enums\UserRole;
use Mockery;
use Tests\TestCase;

class DeleteTaskUseCaseTest extends TestCase
{
    private $taskRepositoryMock;
    private DeleteTaskUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->taskRepositoryMock = Mockery::mock(TaskRepositoryInterface::class);
        $this->useCase = new DeleteTaskUseCase($this->taskRepositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_deletes_task_when_admin()
    {
        $admin = new User(['role' => UserRole::ADMIN->value]);
        $task = new Task();

        $this->taskRepositoryMock
            ->shouldReceive('softDelete')
            ->once()
            ->with($task);

        $this->useCase->execute($admin, $task);
    }

    /** @test */
    public function it_throws_exception_when_not_admin()
    {
        $regularUser = new User(['role' => UserRole::USER->value]);
        $task = new Task();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Acesso negado: apenas administradores podem excluir tarefas');
        $this->expectExceptionCode(403);

        $this->useCase->execute($regularUser, $task);
    }

    /** @test */
    public function it_handles_soft_delete_failure()
    {
        $admin = new User(['role' => UserRole::ADMIN->value]);
        $task = new Task();

        $this->taskRepositoryMock
            ->shouldReceive('softDelete')
            ->once()
            ->andThrow(new \Exception('Database error'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database error');

        $this->useCase->execute($admin, $task);
    }
}
