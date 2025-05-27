<?php

namespace Tests\Unit\Application\UseCases\Users;

use App\Application\UseCases\Users\LogoutUserUseCase;
use Mockery;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class LogoutUserUseCaseTest extends TestCase
{
    private LogoutUserUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->useCase = new LogoutUserUseCase();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_invalidates_current_token()
    {
        $mockToken = Mockery::mock('Tymon\JWTAuth\Token');

        JWTAuth::shouldReceive('getToken')
            ->once()
            ->andReturn($mockToken);

        JWTAuth::shouldReceive('invalidate')
            ->once()
            ->with($mockToken)
            ->andReturn(true);

        $this->useCase->execute();

        $this->assertTrue(true);
    }

    /** @test */
    public function it_handles_token_invalidation_failure()
    {
        $mockToken = Mockery::mock('Tymon\JWTAuth\Token');

        JWTAuth::shouldReceive('getToken')
            ->once()
            ->andReturn($mockToken);

        JWTAuth::shouldReceive('invalidate')
            ->once()
            ->with($mockToken)
            ->andThrow(new \Exception('Falha ao invalidar token'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Falha ao invalidar token');

        $this->useCase->execute();
    }
}
