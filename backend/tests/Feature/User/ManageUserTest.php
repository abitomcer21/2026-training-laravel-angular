<?php

namespace Tests\Feature\User;

use App\User\Infrastructure\Persistence\Models\EloquentUser;
use App\Restaurants\Infrastructure\Persistence\Models\EloquentRestaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ManageUserTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private EloquentRestaurant $restaurante;
    private EloquentUser $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurante = EloquentRestaurant::factory()->create();
        $this->usuario = EloquentUser::factory()->create([
            'restaurant_id' => $this->restaurante->id,
            'email' => 'test@example.com',
            'password' => bcrypt('secret123'),
            'pin' => '1234',
        ]);
    }

    #[Test]
    public function usuario_puede_registrarse(): void
    {
        $data = [
            'name'                  => 'Nuevo Usuario',
            'email'                 => 'nuevo@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'pin'                   => '5678',
            'role'                  => 'camarero',
            'restaurant_id'         => $this->restaurante->id,
        ];

        $response = $this->postJson('/api/users', $data);

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'name', 'email', 'role', 'pin', 'restaurant_id']);

        $this->assertDatabaseHas('users', [
            'email'         => 'nuevo@example.com',
            'name'          => 'Nuevo Usuario',
            'restaurant_id' => $this->restaurante->id,
        ]);
    }

    #[Test]
    public function usuario_puede_iniciar_sesion(): void
    {
        $data = [
            'email'    => 'test@example.com',
            'password' => 'secret123',
        ];

        $response = $this->postJson('/api/login', $data);

        $response->assertStatus(200)
            ->assertJsonStructure(['token']);
    }

    #[Test]
    public function usuario_puede_cerrar_sesion(): void
    {
        $token = $this->usuario->createToken('test')->plainTextToken;
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');
        $response->assertStatus(200);
    }
    #[Test]
    public function usuario_puede_obtener_su_perfil(): void
    {
        Sanctum::actingAs($this->usuario);
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'id'    => $this->usuario->uuid,
                'email' => 'test@example.com',
                'name'  => $this->usuario->name,
            ]);
    }
    #[Test]
    public function usuario_puede_listar_usuarios_de_su_restaurante(): void
    {
        Sanctum::actingAs($this->usuario);

        EloquentUser::factory()->count(2)->create([
            'restaurant_id' => $this->restaurante->id,
        ]);
        $otroRestaurante = EloquentRestaurant::factory()->create();
        EloquentUser::factory()->create(['restaurant_id' => $otroRestaurante->id]);

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'users')
            ->assertJsonStructure([
                'users' => [
                    '*' => ['uuid', 'name', 'email', 'role', 'pin', 'restaurant_id']
                ],
                'total'
            ]);
    }
    #[Test]
    public function usuario_puede_obtener_usuario_por_email(): void
    {
        Sanctum::actingAs($this->usuario);
        $response = $this->getJson('/api/users/email/test@example.com');

        $response->assertStatus(200)
            ->assertJson(['email' => 'test@example.com']);
    }

    #[Test]
    public function usuario_puede_obtener_usuario_por_id(): void
    {
        Sanctum::actingAs($this->usuario);
        $response = $this->getJson("/api/users/{$this->usuario->uuid}");

        $response->assertStatus(200)
            ->assertJson(['id' => $this->usuario->uuid]);
    }

    #[Test]
    public function usuario_puede_actualizar_su_propio_usuario(): void
    {
        Sanctum::actingAs($this->usuario);
        $data = ['name' => 'Nombre Actualizado'];

        $response = $this->putJson("/api/users/{$this->usuario->uuid}", $data);

        $response->assertStatus(200)
            ->assertJson(['name' => 'Nombre Actualizado']);

        $this->assertDatabaseHas('users', [
            'uuid' => $this->usuario->uuid,
            'name' => 'Nombre Actualizado',
        ]);
    }

    #[Test]
    public function usuario_no_puede_actualizar_usuario_de_otro_restaurante(): void
    {
        Sanctum::actingAs($this->usuario);
        $otroRestaurante = EloquentRestaurant::factory()->create();
        $otroUsuario = EloquentUser::factory()->create(['restaurant_id' => $otroRestaurante->id]);

        $response = $this->putJson("/api/users/{$otroUsuario->uuid}", ['name' => 'Intento']);
        $response->assertStatus(404);
    }

    #[Test]
    public function usuario_puede_eliminar_su_propio_usuario(): void
    {
        Sanctum::actingAs($this->usuario);
        $response = $this->deleteJson("/api/users/{$this->usuario->uuid}");
        $response->assertStatus(204);
        $this->assertSoftDeleted('users', ['uuid' => $this->usuario->uuid]);
    }

    #[Test]
    public function usuario_no_puede_eliminar_usuario_de_otro_restaurante(): void
    {
        Sanctum::actingAs($this->usuario);
        $otroRestaurante = EloquentRestaurant::factory()->create();
        $otroUsuario = EloquentUser::factory()->create(['restaurant_id' => $otroRestaurante->id]);

        $response = $this->deleteJson("/api/users/{$otroUsuario->uuid}");
        $response->assertStatus(404);
    }

    #[Test]
    public function usuario_puede_validar_su_pin(): void
    {
        Sanctum::actingAs($this->usuario);
        $response = $this->postJson("/api/users/{$this->usuario->uuid}/validate-pin", ['pin' => '1234']);

        $response->assertStatus(200)
            ->assertJson(['valid' => true]);
    }

    #[Test]
    public function usuario_no_puede_validar_pin_incorrecto(): void
    {
        Sanctum::actingAs($this->usuario);
        $response = $this->postJson("/api/users/{$this->usuario->uuid}/validate-pin", ['pin' => '0000']);

        $response->assertStatus(200)
            ->assertJson(['valid' => false]);
    }

    #[Test]
    public function valida_campos_requeridos_al_registrar(): void
    {
        $response = $this->postJson('/api/users', []);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password', 'pin', 'role', 'restaurant_id']);
    }

    #[Test]
    public function valida_email_unico_al_registrar(): void
    {
        $data = [
            'name'                  => 'Otro Usuario',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'pin'                   => '1111',
            'role'                  => 'camarero',
            'restaurant_id'         => $this->restaurante->id,
        ];

        $response = $this->postJson('/api/users', $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
