<?php

namespace Tests\Feature\Restaurant;

use App\Restaurants\Infrastructure\Persistence\Models\EloquentRestaurant;
use App\User\Infrastructure\Persistence\Models\EloquentUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ManageRestaurantTest extends TestCase
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
        ]);
    }

    #[Test]
    public function usuario_puede_crear_un_restaurante(): void
    {
        $data = [
            'name'       => 'Mi Restaurante',
            'legal_name' => 'Mi Restaurante S.L.',
            'email'      => 'contacto@mirestaurante.com',
            'password'   => 'secret123',
            'tax_id'     => 'B12345678',
        ];

        $response = $this->postJson('/api/restaurants', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id', 'name', 'legal_name', 'tax_id', 'email', 'created_at', 'updated_at'
            ]);

        $this->assertDatabaseHas('restaurants', [
            'name'  => 'Mi Restaurante',
            'email' => 'contacto@mirestaurante.com',
        ]);
    }

    #[Test]
    public function usuario_puede_obtener_su_restaurante(): void
    {
        Sanctum::actingAs($this->usuario);

        $response = $this->getJson('/api/my-restaurant');

        $response->assertStatus(200)
            ->assertJson([
                'id'   => $this->restaurante->uuid,
                'name' => $this->restaurante->name,
            ]);
    }

    #[Test]
    public function usuario_no_autenticado_no_puede_obtener_restaurante(): void
    {
        $response = $this->getJson('/api/my-restaurant');
        $response->assertStatus(401);
    }

    #[Test]
    public function valida_campos_requeridos_al_crear_restaurante(): void
    {
        $response = $this->postJson('/api/restaurants', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'legal_name', 'tax_id', 'password']);
    }

    #[Test]
    public function valida_email_unico_al_crear_restaurante(): void
    {
        EloquentRestaurant::factory()->create(['email' => 'duplicado@example.com']);

        $data = [
            'name'       => 'Otro',
            'legal_name' => 'Otro S.L.',
            'email'      => 'duplicado@example.com',
            'password'   => 'secret123',
            'tax_id'     => 'B87654321',
        ];

        $response = $this->postJson('/api/restaurants', $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function valida_tax_id_unico_al_crear_restaurante(): void
    {
        EloquentRestaurant::factory()->create(['tax_id' => 'B12345678']);

        $data = [
            'name'       => 'Otro',
            'legal_name' => 'Otro S.L.',
            'email'      => 'otro@example.com',
            'password'   => 'secret123',
            'tax_id'     => 'B12345678',
        ];

        $response = $this->postJson('/api/restaurants', $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['tax_id']);
    }
}