<?php

namespace Tests\Feature\Product;

use App\Products\Infrastructure\Persistence\Models\EloquentProduct;
use App\User\Infrastructure\Persistence\Models\EloquentUser;
use App\Restaurants\Infrastructure\Persistence\Models\EloquentRestaurant;
use App\Family\Infrastructure\Persistence\Models\EloquentFamily;
use App\Tax\Infrastructure\Persistence\Models\EloquentTax;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ManageProductTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private EloquentUser $usuario;
    private EloquentRestaurant $restaurante;
    private EloquentFamily $familia;
    private EloquentTax $tax;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurante = EloquentRestaurant::factory()->create();
        $this->familia = EloquentFamily::factory()->create([
            'restaurant_id' => $this->restaurante->id,
        ]);
        $this->tax = EloquentTax::factory()->create([
            'restaurant_id' => $this->restaurante->id,
        ]);
        $this->usuario = EloquentUser::factory()->create([
            'restaurant_id' => $this->restaurante->id,
        ]);

        Sanctum::actingAs($this->usuario);
    }

#[Test]
public function usuario_puede_crear_un_producto(): void
{
    $data = [
        'family_id'     => $this->familia->uuid,
        'tax_id'        => $this->tax->uuid,
        'restaurant_id' => $this->restaurante->id,
        'name'          => 'Producto Test',
        'price'         => 1099,
        'stock'         => 100,
        'image_src'     => 'https://ejemplo.com/imagen.jpg', 
        'active'        => true,
    ];

    $response = $this->postJson('/api/products', $data);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'id', 'name', 'price', 'stock', 'active', 'restaurant_id', 'family_id'
        ]);

    $this->assertDatabaseHas('products', [
        'name'          => 'Producto Test',
        'restaurant_id' => $this->restaurante->id,
    ]);
}

    #[Test]
    public function usuario_puede_listar_sus_productos(): void
    {
        EloquentProduct::factory()->count(3)->create([
            'restaurant_id' => $this->restaurante->id,
        ]);

        $otroRestaurante = EloquentRestaurant::factory()->create();
        EloquentProduct::factory()->create([
            'restaurant_id' => $otroRestaurante->id,
        ]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'products')
            ->assertJsonPath('total', 3)
            ->assertJsonStructure([
                'products' => [
                    '*' => ['id', 'name', 'price', 'stock', 'active', 'restaurant_id']
                ],
                'total',
            ]);
    }

    #[Test]
    public function usuario_puede_obtener_un_producto_por_id(): void
    {
        $producto = EloquentProduct::factory()->create([
            'restaurant_id' => $this->restaurante->id,
            'name' => 'Producto Especial',
        ]);

        $response = $this->getJson("/api/products/{$producto->uuid}");

        $response->assertStatus(200)
            ->assertJson([
                'id'   => $producto->uuid,
                'name' => 'Producto Especial',
            ]);
    }

    #[Test]
    public function usuario_no_puede_obtener_producto_de_otro_restaurante(): void
    {
        $otroRestaurante = EloquentRestaurant::factory()->create();
        $producto = EloquentProduct::factory()->create([
            'restaurant_id' => $otroRestaurante->id,
        ]);

        $response = $this->getJson("/api/products/{$producto->uuid}");
        $response->assertStatus(404);
    }

    #[Test]
    public function usuario_puede_actualizar_su_producto(): void
    {
        $producto = EloquentProduct::factory()->create([
            'restaurant_id' => $this->restaurante->id,
            'name' => 'Nombre Viejo',
            'price' => 1000,
        ]);

        $data = [
            'name'  => 'Nombre Nuevo',
            'price' => 1500,
        ];

        $response = $this->putJson("/api/products/{$producto->uuid}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'id'    => $producto->uuid,
                'name'  => 'Nombre Nuevo',
                'price' => 1500,
            ]);

        $this->assertDatabaseHas('products', [
            'uuid'  => $producto->uuid,
            'name'  => 'Nombre Nuevo',
            'price' => 1500,
        ]);
    }

    #[Test]
    public function usuario_no_puede_actualizar_producto_de_otro_restaurante(): void
    {
        $otroRestaurante = EloquentRestaurant::factory()->create();
        $producto = EloquentProduct::factory()->create([
            'restaurant_id' => $otroRestaurante->id,
        ]);

        $response = $this->putJson("/api/products/{$producto->uuid}", ['name' => 'Intento']);
        $response->assertStatus(404);
    }

    #[Test]
    public function usuario_puede_eliminar_su_producto(): void
    {
        $producto = EloquentProduct::factory()->create([
            'restaurant_id' => $this->restaurante->id,
        ]);

        $response = $this->deleteJson("/api/products/{$producto->uuid}");
        $response->assertStatus(204);
        $this->assertSoftDeleted('products', ['uuid' => $producto->uuid]);
    }

    #[Test]
    public function usuario_no_puede_eliminar_producto_de_otro_restaurante(): void
    {
        $otroRestaurante = EloquentRestaurant::factory()->create();
        $producto = EloquentProduct::factory()->create([
            'restaurant_id' => $otroRestaurante->id,
        ]);

        $response = $this->deleteJson("/api/products/{$producto->uuid}");
        $response->assertStatus(404);
    }

    #[Test]
    public function usuario_puede_listar_productos_por_familia(): void
    {
        EloquentProduct::factory()->count(2)->create([
            'family_id'     => $this->familia->uuid,
            'restaurant_id' => $this->restaurante->id,
        ]);

        $response = $this->getJson("/api/products/family/{$this->familia->uuid}");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'products')
            ->assertJsonPath('total', 2)
            ->assertJsonStructure([
                'products' => [
                    '*' => ['id', 'name', 'price']
                ],
                'total',
            ]);
    }

    #[Test]
    public function usuario_puede_obtener_producto_por_nombre(): void
    {
        $producto = EloquentProduct::factory()->create([
            'restaurant_id' => $this->restaurante->id,
            'name' => 'ProductoUnico',
        ]);

        $response = $this->getJson("/api/products/name/ProductoUnico");

        $response->assertStatus(200)
            ->assertJson([
                'id'   => $producto->uuid,
                'name' => 'ProductoUnico',
            ]);
    }

    #[Test]
    public function usuario_no_puede_obtener_producto_por_nombre_de_otro_restaurante(): void
    {
        $otroRestaurante = EloquentRestaurant::factory()->create();
        EloquentProduct::factory()->create([
            'restaurant_id' => $otroRestaurante->id,
            'name' => 'ProductoAjeno',
        ]);

        $response = $this->getJson('/api/products/name/ProductoAjeno');
        $response->assertStatus(404);
    }

    #[Test]
    public function valida_campos_requeridos_al_crear(): void
    {
        $response = $this->postJson('/api/products', []);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['family_id', 'name', 'price', 'stock']);
    }

    #[Test]
    public function valida_que_el_precio_sea_positivo(): void
    {
        $data = [
            'family_id' => $this->familia->uuid,
            'name'      => 'Producto',
            'price'     => -100,
            'stock'     => 10,
        ];

        $response = $this->postJson('/api/products', $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    #[Test]
    public function valida_que_el_stock_no_sea_negativo(): void
    {
        $data = [
            'family_id' => $this->familia->uuid,
            'name'      => 'Producto',
            'price'     => 1000,
            'stock'     => -5,
        ];

        $response = $this->postJson('/api/products', $data);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['stock']);
    }
}