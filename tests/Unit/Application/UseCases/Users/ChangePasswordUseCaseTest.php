<?php

namespace Tests\Unit\Application\UseCases\Users;

use App\Application\DTOs\ChangePasswordDTO;
use App\Application\UseCases\Users\ChangePasswordUseCase;
use App\Domain\Users\Entities\User;
use App\Domain\Users\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class ChangePasswordUseCaseTest extends TestCase
{
    private $userRepositoryMock;
    private ChangePasswordUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepositoryMock = Mockery::mock(UserRepositoryInterface::class);
        $this->useCase = new ChangePasswordUseCase($this->userRepositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_changes_password_successfully()
    {
        $user = new User([
            'email' => 'user@example.com',
            'password' => Hash::make('currentPassword123')
        ]);

        $dto = new ChangePasswordDTO(
            current_password: 'currentPassword123',
            new_password: 'newSecurePassword456'
        );

        $this->userRepositoryMock
            ->shouldReceive('updatePassword')
            ->once()
            ->with($user, 'newSecurePassword456');

        $this->useCase->execute($user, $dto);

        $this->assertTrue(true);
    }

    /** @test */
    public function it_throws_exception_for_wrong_current_password()
    {
        $user = new User([
            'email' => 'user@example.com',
            'password' => Hash::make('currentPassword123')
        ]);

        $dto = new ChangePasswordDTO(
            current_password: 'wrongPassword',
            new_password: 'newSecurePassword456'
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Senha atual incorreta');
        $this->expectExceptionCode(401);

        $this->useCase->execute($user, $dto);
    }

    /** @test */
    public function it_throws_exception_when_repository_fails()
    {
        $user = new User([
            'email' => 'user@example.com',
            'password' => Hash::make('currentPassword123')
        ]);

        $dto = new ChangePasswordDTO(
            current_password: 'currentPassword123',
            new_password: 'newSecurePassword456'
        );

        $this->userRepositoryMock
            ->shouldReceive('updatePassword')
            ->once()
            ->andThrow(new \Exception('Database error', 500));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database error');
        $this->expectExceptionCode(500);

        $this->useCase->execute($user, $dto);
    }
}
