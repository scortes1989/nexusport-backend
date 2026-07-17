<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that categories list endpoint returns categories successfully.
     */
    public function test_can_list_categories(): void
    {
        // Arrange: Create 3 categories using the factory
        $categories = Category::factory()->count(3)->create();

        // Act: Request the category list api
        $response = $this->getJson('/api/categories');

        // Assert: Verify status code and JSON structure
        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'slug',
                'description',
                'created_at',
                'updated_at',
            ]
        ]);

        // Assert: Verify first category matches DB content
        $response->assertJsonFragment([
            'id' => $categories[0]->id,
            'name' => $categories[0]->name,
            'slug' => $categories[0]->slug,
        ]);
    }
}
