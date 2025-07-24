<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_un_usuario_se_puede_registrar_con_datos_validos()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Juan',
            'email' => 'juan@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['user', 'token']);
        $this->assertDatabaseHas('users', ['email' => 'juan@example.com']);
    }
}
