<?php

namespace Tests\Unit\Controllers\Users;

use App\Application\DTOs\ChangePasswordDTO;
use App\Application\UseCases\Users\ChangePasswordUseCase;
use App\Domain\Users\Entities\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ChangePasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    private $useCaseMock;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useCaseMock = Mockery::mock(ChangePasswordUseCase::class);
        $this->app->instance(ChangePasswordUseCase::class, $this->useCaseMock);

        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_changes_password_successfully()
    {
        $payload = [
            'current_password' => 'old_password',
            'new_password' => 'new_password123'
        ];

        $this->useCaseMock->shouldReceive('execute')
            ->with(Mockery::type(User::class), Mockery::type(ChangePasswordDTO::class))
            ->once();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/users/password', $payload);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Senha alterada com sucesso']);
    }

    public function test_it_fails_with_wrong_current_password()
    {
        $payload = [
            'current_password' => 'wrong_password',
            'new_password' => 'new_password123'
        ];

        $this->useCaseMock->shouldReceive('execute')
            ->andThrow(new \Exception('Senha atual incorreta', 401));

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/users/password', $payload);

        $response->assertStatus(401)
            ->assertJson(['error' => 'Senha atual incorreta']);
    }

    public function test_it_validates_password_rules()
    {
        $invalidPayloads = [
            [
                'current_password' => 'old_password',
                'new_password' => 'short'
            ],
            [
                'current_password' => 'old_password',
                'new_password' => 'old_password'
            ],
            [
                'current_password' => '',
                'new_password' => 'valid_password'
            ]
        ];

        foreach ($invalidPayloads as $payload) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->token
            ])->putJson('/api/users/password', $payload);

            $response->assertStatus(422);
        }
    }

    public function test_it_requires_authentication()
    {
        $payload = [
            'current_password' => 'old_password',
            'new_password' => 'new_password123'
        ];

        $response = $this->putJson('/api/users/password', $payload);

        $response->assertStatus(401);
    }
}
