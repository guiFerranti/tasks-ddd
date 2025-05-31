<?php

namespace Tests\Unit\Controllers\Auth;

use App\Application\UseCases\Users\LogoutUserUseCase;
use App\Domain\Users\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    private $useCaseMock;
    private $user;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useCaseMock = Mockery::mock(LogoutUserUseCase::class);
        $this->app->instance(LogoutUserUseCase::class, $this->useCaseMock);

        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function test_it_logs_out_successfully()
    {
        $this->useCaseMock->shouldReceive('execute')
            ->once();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logout realizado com sucesso']);
    }

    /** @test */
    public function test_it_requires_authentication()
    {
        $response = $this->postJson('/api/auth/logout');
        $response->assertStatus(401);
    }

    /** @test */
    public function test_it_handles_logout_failure()
    {
        $this->useCaseMock->shouldReceive('execute')
            ->andThrow(new \Exception('Logout failed'));

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/auth/logout');

        $response->assertStatus(500);
    }
}
