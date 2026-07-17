<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that products list endpoint returns products successfully with category data formatted via ProductResource.
     */
    public function test_can_list_products(): void
    {
        // Arrange: Create 3 products using the factory
        $products = Product::factory()->count(3)->create();

        // Act: Request the products list api
        $response = $this->getJson('/api/products');

        // Assert: Verify status code and JSON structure wrapped in 'data' key
        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'longDescription',
                    'price',
                    'category',
                    'gradient',
                    'rating',
                    'reviewsCount',
                    'specs',
                    'featured',
                    'stock',
                ]
            ]
        ]);

        // Assert: Verify details match DB records through Resource mapping
        $response->assertJsonFragment([
            'id' => $products[0]->id,
            'name' => $products[0]->name,
            'price' => (float) $products[0]->price,
            'category' => $products[0]->category->name,
            'longDescription' => $products[0]->long_description,
            'reviewsCount' => (int) $products[0]->reviews_count,
        ]);
    }
}
