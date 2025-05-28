<?php

namespace Tests\Unit\Application\UseCases\Users;

use App\Application\UseCases\Users\DeleteUserUseCase;
use App\Domain\Users\Entities\User;
use Mockery;
use Tests\TestCase;

class DeleteUserUseCaseTest extends TestCase
{
    private DeleteUserUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useCase = new DeleteUserUseCase();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_deletes_user_when_admin()
    {
        $admin = new User(['role' => 'admin']);
        $targetUser = Mockery::mock(User::class);

        $targetUser->shouldReceive('delete')->once();

        $this->useCase->execute($admin, $targetUser);
    }

    /** @test */
    public function it_throws_exception_when_not_admin()
    {
        $regularUser = new User(['role' => 'user']);
        $targetUser = new User();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Acesso negado: apenas administradores podem excluir usuários');
        $this->expectExceptionCode(403);

        $this->useCase->execute($regularUser, $targetUser);
    }

    /** @test */
    public function it_allows_admin_to_delete_another_admin()
    {
        $admin = new User(['role' => 'admin']);
        $targetAdmin = Mockery::mock(User::class);
        $targetAdmin->shouldReceive('delete')->once();

        $this->useCase->execute($admin, $targetAdmin);
    }
}
