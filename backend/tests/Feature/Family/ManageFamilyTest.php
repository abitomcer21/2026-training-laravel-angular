<?php

namespace Tests\Feature\Family;

use App\Family\Infrastructure\Persistence\Models\EloquentFamily;
use App\User\Infrastructure\Persistence\Models\EloquentUser;
use App\Restaurants\Infrastructure\Persistence\Models\EloquentRestaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ManageFamilyTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private EloquentUser $user;
    private EloquentRestaurant $restaurant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = EloquentRestaurant::factory()->create();

        $this->user = EloquentUser::factory()->create([
            'restaurant_id' => $this->restaurant->id,
        ]);

        Sanctum::actingAs($this->user);
    }

    #[Test]
    public function user_can_create_a_family(): void
    {
        $data = [
            'name'          => 'Frutas',
            'active'        => true,
            'restaurant_id' => $this->restaurant->id,
        ];

        $response = $this->postJson('/api/family', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'active'
            ]);

        $this->assertDatabaseHas('families', [
            'name'          => 'Frutas',
            'active'        => true,
            'restaurant_id' => $this->restaurant->id,
        ]);
    }

    #[Test]
    public function user_can_list_their_own_families(): void
    {
        for ($i = 1; $i <= 2; $i++) {
            EloquentFamily::factory()->create([
                'restaurant_id' => $this->restaurant->id,
                'name'          => "Familia {$i}",
            ]);
        }

        $otherRestaurant = EloquentRestaurant::factory()->create();
        EloquentFamily::factory()->create([
            'restaurant_id' => $otherRestaurant->id,
            'name'          => 'Otra Familia',
        ]);

        $response = $this->getJson('/api/family');
        $data = $response->json();

        if (isset($data['data'])) {
            $this->assertCount(2, $data['data']);
        } else {
            $this->assertCount(2, $data);
        }

        $response->assertStatus(200);
    }

    #[Test]
    public function user_can_get_a_specific_family(): void
    {
        $family = EloquentFamily::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'name'          => 'Frutas',
        ]);

        $response = $this->getJson("/api/family/{$family->uuid}");

        $response->assertStatus(200)
            ->assertJson([
                'id'           => $family->uuid,
                'name'         => 'Frutas',
                'active'       => $family->active,
                'restaurantId' => $this->restaurant->id,
            ])
            ->assertJsonStructure([
                'createdAt',
                'updatedAt',
            ]);
    }

    #[Test]
    public function user_cannot_get_family_from_another_restaurant(): void
    {
        $otherRestaurant = EloquentRestaurant::factory()->create();
        $familyFromOther = EloquentFamily::factory()->create([
            'restaurant_id' => $otherRestaurant->id,
        ]);

        $response = $this->getJson("/api/family/{$familyFromOther->uuid}");

        $response->assertStatus(404);
    }

    #[Test]
    public function user_can_update_their_own_family(): void
    {
        $family = EloquentFamily::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'name'          => 'AntiguoNombre',
            'active'        => false,
        ]);

        $data = [
            'name'   => 'NuevoNombre',
            'active' => true,
        ];

        $response = $this->putJson("/api/family/{$family->uuid}", $data);

        $response->assertStatus(200);
        $json = $response->json();
        $this->assertEquals($family->uuid, $json['id']);
        $this->assertEquals('NuevoNombre', $json['name']);
        $this->assertTrue($json['active']);
        $restaurantIdKey = isset($json['restaurantId']) ? 'restaurantId' : 'restaurant_id';
        $this->assertEquals($this->restaurant->id, $json[$restaurantIdKey]);

        $this->assertDatabaseHas('families', [
            'uuid'   => $family->uuid,
            'name'   => 'NuevoNombre',
            'active' => true,
        ]);
    }

    #[Test]
    public function user_cannot_update_family_from_another_restaurant(): void
    {
        $otherRestaurant = EloquentRestaurant::factory()->create();
        $familyFromOther = EloquentFamily::factory()->create([
            'restaurant_id' => $otherRestaurant->id,
        ]);

        $response = $this->putJson("/api/family/{$familyFromOther->uuid}", ['name' => 'IntentoDeCambio']);
        $response->assertStatus(404);
    }

    #[Test]
    public function user_can_delete_their_own_family(): void
    {
        $family = EloquentFamily::factory()->create([
            'restaurant_id' => $this->restaurant->id,
        ]);

        $response = $this->deleteJson("/api/family/{$family->uuid}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('families', ['uuid' => $family->uuid]);
    }

    #[Test]
    public function user_cannot_delete_family_from_another_restaurant(): void
    {
        $otherRestaurant = EloquentRestaurant::factory()->create();
        $familyFromOther = EloquentFamily::factory()->create([
            'restaurant_id' => $otherRestaurant->id,
        ]);

        $response = $this->deleteJson("/api/family/{$familyFromOther->uuid}");
        $response->assertStatus(404);
    }

    #[Test]
    public function it_validates_required_fields_when_creating(): void
    {
        $response = $this->postJson('/api/family', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['restaurant_id']);
    }
    #[Test]
    public function it_validates_unique_name_per_restaurant_when_creating(): void
    {
        EloquentFamily::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'name'          => 'Duplicado',
        ]);

        $response = $this->postJson('/api/family', [
            'name'          => 'Duplicado',
            'active'        => true,
            'restaurant_id' => $this->restaurant->id,
        ]);

        dump($response->json());
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}
