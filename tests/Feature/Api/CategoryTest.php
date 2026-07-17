<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_categories(): void
    {
        $categories = Category::factory()->count(3)->create();

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'description',
                ]
            ]
        ]);

        $response->assertJsonFragment([
            'id' => $categories[0]->id,
            'name' => $categories[0]->name,
            'slug' => $categories[0]->slug,
            'description' => $categories[0]->description,
        ]);
    }
}
