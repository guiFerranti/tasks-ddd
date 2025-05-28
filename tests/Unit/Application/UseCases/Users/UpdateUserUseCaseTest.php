<?php

namespace Tests\Unit\Application\UseCases\Users;

use App\Application\DTOs\UpdateUserDTO;
use App\Application\UseCases\Users\UpdateUserUseCase;
use App\Domain\Users\Entities\User;
use App\Domain\Users\Repositories\UserRepositoryInterface;
use Mockery;
use Tests\TestCase;

class UpdateUserUseCaseTest extends TestCase
{
    private $userRepositoryMock;
    private UpdateUserUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepositoryMock = Mockery::mock(UserRepositoryInterface::class);
        $this->useCase = new UpdateUserUseCase($this->userRepositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_updates_user_with_provided_data()
    {
        $userId = 1;
        $user = new User(['id' => $userId]);
        $dto = new UpdateUserDTO(
            id: $userId,
            name: 'Novo Nome',
            email: 'novo@email.com',
            cpf: '12345678901'
        );

        $this->userRepositoryMock
            ->shouldReceive('findById')
            ->with($userId)
            ->andReturn($user);

        $this->userRepositoryMock
            ->shouldReceive('update')
            ->with($user, [
                'name' => 'Novo Nome',
                'email' => 'novo@email.com',
                'cpf' => '12345678901'
            ])
            ->andReturn($user);

        $result = $this->useCase->execute($userId, $dto);

        $this->assertInstanceOf(User::class, $result);
    }

    /** @test */
    public function it_updates_partial_data()
    {
        $userId = 1;
        $user = new User(['id' => $userId]);
        $dto = new UpdateUserDTO(
            id: $userId,
            name: 'Novo Nome'
        );

        $this->userRepositoryMock
            ->shouldReceive('findById')
            ->with($userId)
            ->andReturn($user);

        $this->userRepositoryMock
            ->shouldReceive('update')
            ->with($user, [
                'name' => 'Novo Nome'
            ])
            ->andReturn($user);

        $result = $this->useCase->execute($userId, $dto);

        $this->assertInstanceOf(User::class, $result);
    }

    /** @test */
    public function it_throws_exception_when_user_not_found()
    {
        $userId = 999;
        $dto = new UpdateUserDTO(
            id: $userId,
            name: 'Novo Nome'
        );

        $this->userRepositoryMock
            ->shouldReceive('findById')
            ->with($userId)
            ->andReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Usuário não encontrado');

        $this->useCase->execute($userId, $dto);
    }

    /** @test */
    public function it_throws_exception_when_ids_mismatch()
    {
        $userId = 1;
        $dto = new UpdateUserDTO(
            id: 2,
            name: 'Novo Nome'
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('IDs não correspondem');

        $this->useCase->execute($userId, $dto);
    }
}
