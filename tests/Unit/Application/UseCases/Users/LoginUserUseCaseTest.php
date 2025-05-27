<?php

namespace Tests\Unit\Application\UseCases\Users;

use App\Application\DTOs\LoginDTO;
use App\Application\UseCases\Users\LoginUserUseCase;
use App\Domain\Users\Repositories\UserRepositoryInterface;
use App\Domain\Users\Entities\User;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginUserUseCaseTest extends TestCase
{
    private $userRepositoryMock;
    private LoginUserUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepositoryMock = Mockery::mock(UserRepositoryInterface::class);
        $this->useCase = new LoginUserUseCase($this->userRepositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_returns_token_for_valid_credentials()
    {
        $user = new User([
            'email' => 'test@example.com',
            'password' => Hash::make('validPassword123')
        ]);

        $dto = new LoginDTO(
            email: 'test@example.com',
            password: 'validPassword123'
        );

        $expectedToken = 'fake.jwt.token';

        $this->userRepositoryMock
            ->shouldReceive('findByEmail')
            ->with('test@example.com')
            ->andReturn($user);

        JWTAuth::shouldReceive('fromUser')
            ->with($user)
            ->andReturn($expectedToken);

        $result = $this->useCase->execute($dto);

        $this->assertEquals(['token' => $expectedToken], $result);
    }

    /** @test */
    public function it_throws_exception_for_invalid_email()
    {
        $dto = new LoginDTO(
            email: 'wrong@example.com',
            password: 'anyPassword123'
        );

        $this->userRepositoryMock
            ->shouldReceive('findByEmail')
            ->with('wrong@example.com')
            ->andReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Credenciais inválidas');
        $this->expectExceptionCode(401);

        $this->useCase->execute($dto);
    }

    /** @test */
    public function it_throws_exception_for_invalid_password()
    {
        $user = new User([
            'email' => 'test@example.com',
            'password' => Hash::make('validPassword123')
        ]);

        $dto = new LoginDTO(
            email: 'test@example.com',
            password: 'wrongPassword'
        );

        $this->userRepositoryMock
            ->shouldReceive('findByEmail')
            ->with('test@example.com')
            ->andReturn($user);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Credenciais inválidas');
        $this->expectExceptionCode(401);

        $this->useCase->execute($dto);
    }
}
