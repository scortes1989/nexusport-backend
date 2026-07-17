<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Chaquetas',
                'slug' => 'chaquetas',
                'description' => 'Cortavientos y chaquetas térmicas de alto rendimiento para entrenar al aire libre.',
            ],
            [
                'name' => 'Calzado',
                'slug' => 'calzado',
                'description' => 'Zapatillas de running, trail y entrenamiento con amortiguación reactiva.',
            ],
            [
                'name' => 'Pantalones',
                'slug' => 'pantalones',
                'description' => 'Calzas de compresión, shorts y calzones deportivos avanzados.',
            ],
            [
                'name' => 'Poleras',
                'slug' => 'poleras',
                'description' => 'Poleras térmicas y camisetas transpirables de secado rápido.',
            ],
            [
                'name' => 'Accesorios',
                'slug' => 'accesorios',
                'description' => 'Mochilas de hidratación, calcetines de tracción y accesorios deportivos esenciales.',
            ],
        ];

        foreach ($categories as $cat) {
            \App\Models\Category::create($cat);
        }
    }

}
