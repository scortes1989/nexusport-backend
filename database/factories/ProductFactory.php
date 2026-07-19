<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => \App\Models\Category::factory(),
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'long_description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 10, 200),
            'gradient' => 'from-orange-500 to-red-600',
            'rating' => $this->faker->randomFloat(2, 3, 5),
            'reviews_count' => $this->faker->numberBetween(1, 100),
            'specs' => [
                ['label' => 'Material', 'value' => 'Poliéster'],
                ['label' => 'Cuidado', 'value' => 'Lavar a máquina'],
            ],
            'featured' => $this->faker->boolean(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Product $product) {
            $product->sizes()->createMany([
                ['size' => 'S', 'stock' => 5],
                ['size' => 'M', 'stock' => 10],
            ]);
        });
    }

}
