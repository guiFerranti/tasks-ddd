<?php

namespace Tests\Unit\Controllers\Users;

use App\Application\DTOs\RegisterUserDTO;
use App\Application\UseCases\Users\RegisterUserUseCase;
use App\Domain\Users\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private $useCaseMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock do use case
        $this->useCaseMock = Mockery::mock(RegisterUserUseCase::class);
        $this->app->instance(RegisterUserUseCase::class, $this->useCaseMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_registers_user_successfully()
    {
        $payload = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'cpf' => '123.456.789-01',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $expectedUser = new User([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'cpf' => '123.456.789-01',
            'role' => 'user'
        ]);

        $this->useCaseMock->shouldReceive('execute')
            ->with(Mockery::type(RegisterUserDTO::class))
            ->andReturn($expectedUser);

        $response = $this->postJson('/api/users', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'cpf' => '123.456.789-01',
                'role' => 'user'
            ]);
    }

    /** @test */
    public function it_returns_validation_errors()
    {
        $invalidPayload = [
            'name' => '',
            'email' => 'invalid-email',
            'cpf' => '123',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ];

        $response = $this->postJson('/api/users', $invalidPayload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'email',
                'cpf',
                'password'
            ]);
    }

    /** @test */
    public function it_handles_registration_failure()
    {
        $payload = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'cpf' => '123.456.789-01',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Configurar o mock para lançar exceção
        $this->useCaseMock->shouldReceive('execute')
            ->with(Mockery::type(RegisterUserDTO::class))
            ->andThrow(new \Exception('Database error'));

        $response = $this->postJson('/api/users', $payload);

        $response->assertStatus(400)
            ->assertJson(['error' => 'Database error']);
    }

    /** @test */
    public function it_requires_password_confirmation()
    {
        $payload = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'cpf' => '123.456.789-01',
            'password' => 'password123',
            // Sem password_confirmation
        ];

        $response = $this->postJson('/api/users', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function it_rejects_duplicate_email_and_cpf()
    {
        // Criar usuário existente no banco
        User::factory()->create([
            'email' => 'existing@example.com',
            'cpf' => '111.222.333-44'
        ]);

        $payload = [
            'name' => 'John Doe',
            'email' => 'existing@example.com', // Email duplicado
            'cpf' => '111.222.333-44', // CPF duplicado
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/users', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'cpf']);
    }
}
