<?php

namespace Tests\Unit\Application\UseCases\Users;

use App\Application\UseCases\Users\ListAllUsersUseCase;
use App\Domain\Users\Entities\User;
use App\Domain\Users\Enums\UserRole;
use App\Domain\Users\Repositories\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

class ListAllUsersUseCaseTest extends TestCase
{
    private $userRepositoryMock;
    private ListAllUsersUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepositoryMock = Mockery::mock(UserRepositoryInterface::class);
        $this->useCase = new ListAllUsersUseCase($this->userRepositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_returns_paginated_users_for_admin()
    {
        $adminUser = new User(['role' => UserRole::ADMIN->value]);
        $paginatedUsers = new LengthAwarePaginator([], 0, 15);

        $this->userRepositoryMock
            ->shouldReceive('findAllPaginated')
            ->with(15)
            ->andReturn($paginatedUsers);

        $result = $this->useCase->execute($adminUser);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    /** @test */
    public function it_throws_exception_for_non_admin_users()
    {
        $regularUser = new User(['role' => UserRole::USER->value]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Acesso negado: apenas administradores');
        $this->expectExceptionCode(403);

        $this->useCase->execute($regularUser);
    }

    /** @test */
    public function it_accepts_custom_per_page_value()
    {
        $adminUser = new User(['role' => UserRole::ADMIN->value]);
        $paginatedUsers = new LengthAwarePaginator([], 0, 10);

        $this->userRepositoryMock
            ->shouldReceive('findAllPaginated')
            ->with(10)
            ->andReturn($paginatedUsers);

        $result = $this->useCase->execute($adminUser, 10);

        $this->assertEquals(10, $result->perPage());
    }
}
