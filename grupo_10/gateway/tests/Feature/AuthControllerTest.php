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
    public function un_usuario_se_puede_registrar_con_datos_validos()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Juan',
            'email' => 'juan@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertCreated()
                 ->assertJsonStructure(['user', 'token']);
        $this->assertDatabaseHas('users', ['email' => 'juan@example.com']);
    }

    /** @test */
    public function no_se_puede_registrar_con_email_duplicado()
    {
        User::factory()->create(['email' => 'repetido@example.com']);

        $response = $this->postJson('/api/register', [
            'name' => 'Luis',
            'email' => 'repetido@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('email');
    }

    /** @test */
    public function no_se_puede_registrar_con_password_muy_corto()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'password' => '123',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('password');
    }

    /** @test */
    public function no_se_puede_registrar_con_rol_invalido()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Sofia',
            'email' => 'sofia@example.com',
            'password' => 'password123',
            'role' => 'superadmin',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('role');
    }

    /** @test */
    public function un_usuario_puede_iniciar_sesion_con_credenciales_validas()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
                 ->assertJsonStructure(['user', 'token']);
    }

    /** @test */
    public function no_se_puede_iniciar_sesion_con_credenciales_invalidas()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'malaClave',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['error' => 'Credenciales inválidas']);
    }

    /** @test */
    public function un_usuario_autenticado_puede_cerrar_sesion()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/logout');

        $response->assertOk()
                 ->assertJson(['message' => 'Sesión cerrada correctamente']);
    }

    /** @test */
    public function un_usuario_autenticado_puede_validar_su_token()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->getJson('/api/validate-token');

        $response->assertOk()
                 ->assertJsonStructure(['user', 'message']);
    }

    /** @test */
    public function validar_token_sin_autenticacion_falla()
    {
        $response = $this->getJson('/api/validate-token');
        $response->assertStatus(401); // No autorizado
    }

    /** @test */
    public function logout_sin_token_retorna_401()
    {
        $response = $this->postJson('/api/logout');
        $response->assertStatus(401);
    }
}
