<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\CartItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_empty_cart(): void
    {
        $response = $this->getJson('/api/cart', [
            'X-Session-ID' => 'test-session-id-123'
        ]);

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
    }

    public function test_cart_fails_without_session_id_header(): void
    {
        $response = $this->getJson('/api/cart');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['session_id']);
    }

    public function test_can_add_item_to_cart(): void
    {
        $product = Product::factory()->create();
        $productSize = $product->sizes[0];

        $response = $this->postJson('/api/cart', [
            'product_id' => $product->id,
            'product_size_id' => $productSize->id,
            'quantity' => 2,
        ], [
            'X-Session-ID' => 'test-session-id-123'
        ]);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'quantity',
                    'productSizeId',
                    'size',
                    'product',
                    'productSize',
                ]
            ]
        ]);

        $this->assertDatabaseHas('cart_items', [
            'session_id' => 'test-session-id-123',
            'product_id' => $product->id,
            'product_size_id' => $productSize->id,
            'quantity' => 2,
        ]);
    }

    public function test_can_update_cart_item_quantity(): void
    {
        $product = Product::factory()->create();
        $productSize = $product->sizes[0];

        $cartItem = CartItem::create([
            'session_id' => 'test-session-id-123',
            'product_id' => $product->id,
            'product_size_id' => $productSize->id,
            'quantity' => 2,
        ]);

        $response = $this->putJson("/api/cart/{$cartItem->id}", [
            'quantity' => 4,
        ], [
            'X-Session-ID' => 'test-session-id-123'
        ]);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');

        $this->assertDatabaseHas('cart_items', [
            'session_id' => 'test-session-id-123',
            'product_id' => $product->id,
            'product_size_id' => $productSize->id,
            'quantity' => 4,
        ]);
    }

    public function test_can_remove_item_from_cart(): void
    {
        $product = Product::factory()->create();
        $productSize = $product->sizes[0];

        $cartItem = CartItem::create([
            'session_id' => 'test-session-id-123',
            'product_id' => $product->id,
            'product_size_id' => $productSize->id,
            'quantity' => 2,
        ]);

        $response = $this->deleteJson("/api/cart/{$cartItem->id}", [], [
            'X-Session-ID' => 'test-session-id-123'
        ]);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('cart_items', [
            'session_id' => 'test-session-id-123',
            'product_id' => $product->id,
            'product_size_id' => $productSize->id,
        ]);
    }

    public function test_enforces_stock_limit_per_size(): void
    {
        $product = Product::factory()->create();
        $productSize = $product->sizes[0];
        // Force the first size to have stock 5
        $productSize->update(['stock' => 5]);

        // Try adding 10 items (more than stock 5)
        $response = $this->postJson('/api/cart', [
            'product_id' => $product->id,
            'product_size_id' => $productSize->id,
            'quantity' => 10,
        ], [
            'X-Session-ID' => 'test-session-id-123'
        ]);

        $response->assertStatus(200);

        // Assert quantity is capped to stock 5 in database
        $this->assertDatabaseHas('cart_items', [
            'session_id' => 'test-session-id-123',
            'product_id' => $product->id,
            'product_size_id' => $productSize->id,
            'quantity' => 5,
        ]);
    }
}
