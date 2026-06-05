<?php

namespace Tests\Feature\Tax;

use App\Tax\Infrastructure\Persistence\Models\EloquentTax;
use App\User\Infrastructure\Persistence\Models\EloquentUser;
use App\Restaurants\Infrastructure\Persistence\Models\EloquentRestaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ManageTaxTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private EloquentUser $usuario;
    private EloquentRestaurant $restaurante;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurante = EloquentRestaurant::factory()->create();
        $this->usuario = EloquentUser::factory()->create([
            'restaurant_id' => $this->restaurante->id,
        ]);

        Sanctum::actingAs($this->usuario);
    }

    #[Test]
    public function usuario_puede_crear_un_impuesto(): void
    {
        $data = [
            'name'          => 'IVA',
            'percentage'    => 21,
            'restaurant_id' => $this->restaurante->id,
        ];

        $response = $this->postJson('/api/tax', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id', 'name', 'percentage', 'restaurant_id', 'created_at', 'updated_at'
            ]);

        $this->assertDatabaseHas('taxes', [
            'name'          => 'IVA',
            'percentage'    => 21,
            'restaurant_id' => $this->restaurante->id,
        ]);
    }

    #[Test]
    public function usuario_puede_listar_sus_impuestos(): void
    {
        EloquentTax::factory()->count(2)->create([
            'restaurant_id' => $this->restaurante->id,
        ]);

        $otroRestaurante = EloquentRestaurant::factory()->create();
        EloquentTax::factory()->create([
            'restaurant_id' => $otroRestaurante->id,
        ]);

        $response = $this->getJson('/api/tax');
        $data = $response->json();

        if (isset($data['data'])) {
            $this->assertCount(2, $data['data']);
        } else {
            $this->assertCount(2, $data);
        }

        $response->assertStatus(200);
    }

    #[Test]
    public function usuario_puede_obtener_un_impuesto_por_id(): void
    {
        $impuesto = EloquentTax::factory()->create([
            'restaurant_id' => $this->restaurante->id,
            'name'          => 'IVA Reducido',
            'percentage'    => 10,
        ]);

        $response = $this->getJson("/api/tax/{$impuesto->uuid}");

        $response->assertStatus(200)
        ->assertJson([
            'id'            => $impuesto->uuid,
            'name'          => 'IVA Reducido',
            'percentage'    => 10,
            'restaurant_id' => $this->restaurante->id,
        ]);
    }

    #[Test]
    public function usuario_no_puede_obtener_impuesto_de_otro_restaurante(): void
    {
        $otroRestaurante = EloquentRestaurant::factory()->create();
        $impuesto = EloquentTax::factory()->create([
            'restaurant_id' => $otroRestaurante->id,
        ]);

        $response = $this->getJson("/api/tax/{$impuesto->uuid}");
        $response->assertStatus(404);
    }

    #[Test]
    public function usuario_puede_actualizar_su_impuesto(): void
    {
        $impuesto = EloquentTax::factory()->create([
            'restaurant_id' => $this->restaurante->id,
            'name'          => 'IVA Antiguo',
            'percentage'    => 21,
        ]);

        $data = [
            'name'       => 'IVA Nuevo',
            'percentage' => 10,
        ];

        $response = $this->putJson("/api/tax/{$impuesto->uuid}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $impuesto->uuid,
                'name'          => 'IVA Nuevo',
                'percentage'    => 10,
                'restaurant_id' => $this->restaurante->id,
            ]);

        $this->assertDatabaseHas('taxes', [
            'uuid' => $impuesto->uuid,
            'name'          => 'IVA Nuevo',
            'percentage'    => 10,
        ]);
    }

    #[Test]
    public function usuario_no_puede_actualizar_impuesto_de_otro_restaurante(): void
    {

        $otroRestaurante = EloquentRestaurant::factory()->create();
        $impuesto = EloquentTax::factory()->create([
            'restaurant_id' => $otroRestaurante->id,
        ]);

        $response = $this->putJson("/api/tax/{$impuesto->uuid}", ['name' => 'Intento']);
        $response->assertStatus(404);
    }

    #[Test]
    public function usuario_puede_eliminar_su_impuesto(): void
    {
        $impuesto = EloquentTax::factory()->create([
            'restaurant_id' => $this->restaurante->id,
        ]);

        $response = $this->deleteJson("/api/tax/{$impuesto->uuid}");
        $response->assertStatus(204);        
        $this->assertSoftDeleted('taxes', ['uuid' => $impuesto->uuid]);
    }

    #[Test]
    public function usuario_no_puede_eliminar_impuesto_de_otro_restaurante(): void
    {

        $otroRestaurante = EloquentRestaurant::factory()->create();
        $impuesto = EloquentTax::factory()->create([
            'restaurant_id' => $otroRestaurante->id,
        ]);

        $response = $this->deleteJson("/api/tax/{$impuesto->uuid}");
        $response->assertStatus(404);
    }

    #[Test]
    public function valida_campos_requeridos_al_crear(): void
    {
        $response = $this->postJson('/api/tax', []);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'percentage', 'restaurant_id']);
    }

    #[Test]
    public function valida_que_el_porcentaje_sea_numero(): void
    {
        $data = [
            'name'          => 'IVA',
            'percentage'    => 'no es numero',
            'restaurant_id' => $this->restaurante->id,
        ];

        $response = $this->postJson('/api/tax', $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['percentage']);
    }

    #[Test]
    public function valida_que_el_porcentaje_este_entre_0_y_100(): void
    {
        $data = [
            'name'          => 'IVA',
            'percentage'    => 150,
            'restaurant_id' => $this->restaurante->id,
        ];

        $response = $this->postJson('/api/tax', $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['percentage']);
    }

    #[Test]
    public function valida_nombre_unico_por_restaurante(): void
    {
        EloquentTax::factory()->create([
            'restaurant_id' => $this->restaurante->id,
            'name'          => 'IVA',
        ]);

        $data = [
            'name'          => 'IVA',
            'percentage'    => 21,
            'restaurant_id' => $this->restaurante->id,
        ];

        $response = $this->postJson('/api/tax', $data);

    
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}