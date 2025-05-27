<?php

namespace Tests\Unit\Application\UseCases\Users;

use App\Application\UseCases\Users\GetUserByIdUseCase;
use App\Domain\Users\Entities\User;
use App\Domain\Users\Repositories\UserRepositoryInterface;
use Mockery;
use Tests\TestCase;

class GetUserByIdUseCaseTest extends TestCase
{
    private $userRepositoryMock;
    private GetUserByIdUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepositoryMock = Mockery::mock(UserRepositoryInterface::class);
        $this->useCase = new GetUserByIdUseCase($this->userRepositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_returns_user_when_found()
    {
        $userId = 1;
        $expectedUser = new User(['id' => $userId, 'name' => 'Test User']);

        $this->userRepositoryMock
            ->shouldReceive('findById')
            ->with($userId)
            ->andReturn($expectedUser);

        $result = $this->useCase->execute($userId);

        $this->assertSame($expectedUser, $result);
    }

    /** @test */
    public function it_returns_null_when_user_not_found()
    {
        $userId = 999;

        $this->userRepositoryMock
            ->shouldReceive('findById')
            ->with($userId)
            ->andReturn(null);

        $result = $this->useCase->execute($userId);

        $this->assertNull($result);
    }
}
