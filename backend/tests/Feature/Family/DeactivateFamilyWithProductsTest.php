<?php

namespace Tests\Feature\Family;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Factories\RestaurantFactory;
use Database\Factories\UserFactory;
use Tests\TestCase;

class DeactivateFamilyWithProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_deactivating_family_deactivates_all_related_products(): void
    {
        // Create a user for authentication
        $user = UserFactory::new()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Login to get authentication token
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('token');
        $headers = ['Authorization' => "Bearer {$token}"];

        // Create a restaurant first (required for family via foreign key)
        $restaurant = RestaurantFactory::new()->create();

        // Create a family with the restaurant's ID
        $familyResponse = $this->postJson('/api/family', [
            'name' => 'Test Family',
            'active' => true,
            'restaurant_id' => $restaurant->id,
        ]);

        $familyResponse->assertStatus(201);

        $familyId = $familyResponse->json('id');
        $this->assertNotNull($familyId, 'Family ID should not be null');

        // Create a tax (required for products)
        $taxResponse = $this->postJson('/api/tax', [
            'name' => 'Test Tax',
            'percentage' => 10,
            'restaurant_id' => $restaurant->id,
        ]);

        $taxResponse->assertStatus(201);
        
        $taxId = $taxResponse->json('id');
        $this->assertNotNull($taxId, 'Tax ID should not be null');

        // Create products related to this family
        $product1Response = $this->postJson('/api/products', [
            'family_id' => $familyId,
            'tax_id' => $taxId,
            'restaurant_id' => $restaurant->id,
            'name' => 'Product 1',
            'price' => 1000,
            'stock' => 10,
            'image_src' => 'image1.jpg',
            'active' => true,
        ]);

        $product1Response->assertStatus(201);
        
        $product1Id = $product1Response->json('id');
        $this->assertNotNull($product1Id, 'Product 1 ID should not be null');

        $product2Response = $this->postJson('/api/products', [
            'family_id' => $familyId,
            'tax_id' => $taxId,
            'restaurant_id' => $restaurant->id,
            'name' => 'Product 2',
            'price' => 2000,
            'stock' => 20,
            'image_src' => 'image2.jpg',
            'active' => true,
        ]);

        $product2Response->assertStatus(201);
        
        $product2Id = $product2Response->json('id');
        $this->assertNotNull($product2Id, 'Product 2 ID should not be null');

        // Verify products are active before deactivation
        $product1Before = $this->getJson("/api/products/{$product1Id}");
        $product1Before->assertStatus(200);
        $this->assertTrue($product1Before->json('active'), 'Product 1 should be active before family deactivation');

        $product2Before = $this->getJson("/api/products/{$product2Id}");
        $product2Before->assertStatus(200);
        $this->assertTrue($product2Before->json('active'), 'Product 2 should be active before family deactivation');

        // Deactivate the family (using authentication headers)
        $updateResponse = $this->putJson("/api/family/{$familyId}", [
            'active' => false,
        ], $headers);
        $updateResponse->assertStatus(200);

        // Verify family is inactive (using authentication headers)
        $familyAfter = $this->getJson("/api/family/{$familyId}", $headers);
        $familyAfter->assertStatus(200);
        $this->assertFalse($familyAfter->json('active'), 'Family should be deactivated');

        // Verify products are now also inactive (cascade deactivation)
        $product1Inactive = $this->getJson("/api/products/{$product1Id}");
        $product1Inactive->assertStatus(200);
        $this->assertFalse($product1Inactive->json('active'), 'Product 1 should be automatically deactivated when family is deactivated');

        $product2Inactive = $this->getJson("/api/products/{$product2Id}");
        $product2Inactive->assertStatus(200);
        $this->assertFalse($product2Inactive->json('active'), 'Product 2 should be automatically deactivated when family is deactivated');

        // Reactivate the family
        $reactivateResponse = $this->putJson("/api/family/{$familyId}", [
            'active' => true,
        ], $headers);
        $reactivateResponse->assertStatus(200);

        // Verify family is active again
        $familyReactivated = $this->getJson("/api/family/{$familyId}", $headers);
        $familyReactivated->assertStatus(200);
        $this->assertTrue($familyReactivated->json('active'), 'Family should be reactivated');

        // Verify products are now also active again (cascade reactivation)
        $product1Reactivated = $this->getJson("/api/products/{$product1Id}");
        $product1Reactivated->assertStatus(200);
        $this->assertTrue($product1Reactivated->json('active'), 'Product 1 should be automatically reactivated when family is reactivated');

        $product2Reactivated = $this->getJson("/api/products/{$product2Id}");
        $product2Reactivated->assertStatus(200);
        $this->assertTrue($product2Reactivated->json('active'), 'Product 2 should be automatically reactivated when family is reactivated');
    }
}
