<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products(): void
    {
        $products = Product::factory()->count(3)->create();

        $response = $this->getJson('/api/products');

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
                    'sizes',
                    'imageUrl',
                ]
            ]
        ]);

        $response->assertJsonFragment([
            'id' => $products[0]->id,
            'name' => $products[0]->name,
            'price' => (float) $products[0]->price,
            'category' => $products[0]->category->name,
            'longDescription' => $products[0]->long_description,
            'reviewsCount' => (int) $products[0]->reviews_count,
            'imageUrl' => $products[0]->image_url,
        ]);
    }

    public function test_can_show_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
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
                'sizes',
                'imageUrl',
            ]
        ]);

        $response->assertJsonFragment([
            'id' => $product->id,
            'name' => $product->name,
            'price' => (float) $product->price,
            'category' => $product->category->name,
            'longDescription' => $product->long_description,
            'reviewsCount' => (int) $product->reviews_count,
            'imageUrl' => $product->image_url,
        ]);
    }
}

