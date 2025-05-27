<?php

namespace Tests\Unit\Application\UseCases\Users;

use App\Application\DTOs\RegisterUserDTO;
use App\Application\UseCases\Users\RegisterUserUseCase;
use App\Domain\Users\Entities\User;
use App\Domain\Users\Enums\UserRole;
use App\Domain\Users\Repositories\UserRepositoryInterface;
use Mockery;
use Tests\TestCase;

class RegisterUserUseCaseTest extends TestCase
{
    private $userRepositoryMock;
    private RegisterUserUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepositoryMock = Mockery::mock(UserRepositoryInterface::class);
        $this->useCase = new RegisterUserUseCase($this->userRepositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_creates_user_with_correct_data()
    {
        $dto = new RegisterUserDTO(
            name: 'John Doe',
            email: 'john@example.com',
            cpf: '12345678901',
            password: 'secret123'
        );

        $expectedUser = new User([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'cpf' => '12345678901',
            'password' => bcrypt('secret123'),
            'role' => UserRole::USER->value
        ]);

        $this->userRepositoryMock
            ->shouldReceive('create')
            ->with(Mockery::on(function ($data) use ($dto) {
                return $data['name'] === $dto->name &&
                    $data['email'] === $dto->email &&
                    $data['cpf'] === $dto->cpf &&
                    $data['role'] === UserRole::USER->value &&
                    password_verify($dto->password, $data['password']);
            }))
            ->andReturn($expectedUser);

        $result = $this->useCase->execute($dto);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('John Doe', $result->name);
        $this->assertEquals('john@example.com', $result->email);
        $this->assertEquals(UserRole::USER->value, $result->role);
    }

    /** @test */
    public function it_throws_exception_when_repository_fails()
    {
        $dto = new RegisterUserDTO(
            name: 'John Doe',
            email: 'john@example.com',
            cpf: '12345678901',
            password: 'secret123'
        );

        $this->userRepositoryMock
            ->shouldReceive('create')
            ->andThrow(new \Exception('Database error'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database error');

        $this->useCase->execute($dto);
    }
}
