<?php

namespace Tests\Unit\Controllers\Auth;

use App\Application\DTOs\LoginDTO;
use App\Application\UseCases\Users\LoginUserUseCase;
use App\Domain\Users\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private $useCaseMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useCaseMock = Mockery::mock(LoginUserUseCase::class);
        $this->app->instance(LoginUserUseCase::class, $this->useCaseMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function test_it_logs_in_successfully()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $payload = [
            'email' => 'test@example.com',
            'password' => 'password123'
        ];

        $token = JWTAuth::fromUser($user);

        $this->useCaseMock->shouldReceive('execute')
            ->with(Mockery::type(LoginDTO::class))
            ->andReturn(['token' => $token]);

        $response = $this->postJson('/api/auth/login', $payload);

        $response->assertStatus(200)
            ->assertJsonStructure(['token']);
    }

    /** @test */
    public function test_it_returns_unauthorized_with_invalid_credentials()
    {
        $payload = [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword'
        ];

        $this->useCaseMock->shouldReceive('execute')
            ->andThrow(new \Exception('Credenciais inválidas', 401));

        $response = $this->postJson('/api/auth/login', $payload);

        $response->assertStatus(401)
            ->assertJson(['error' => 'Credenciais inválidas']);
    }

    /** @test */
    public function test_it_validates_login_input()
    {
        $invalidPayloads = [
            ['email' => 'not-an-email', 'password' => '123'],
            ['email' => 'valid@email.com', 'password' => ''],
            ['email' => '', 'password' => 'validpassword']
        ];

        foreach ($invalidPayloads as $payload) {
            $response = $this->postJson('/api/auth/login', $payload);
            $response->assertStatus(422);
        }
    }

    public function test_it_requires_email_and_password()
    {
        $response = $this->postJson('/api/auth/login', []);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }
}
