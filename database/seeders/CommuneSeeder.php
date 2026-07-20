<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommuneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $communes = [
            ['name' => 'Santiago', 'shipping_price' => 3000.00],
            ['name' => 'Providencia', 'shipping_price' => 3000.00],
            ['name' => 'Las Condes', 'shipping_price' => 3500.00],
            ['name' => 'Vitacura', 'shipping_price' => 3500.00],
            ['name' => 'Ñuñoa', 'shipping_price' => 3000.00],
            ['name' => 'La Reina', 'shipping_price' => 3500.00],
            ['name' => 'Macul', 'shipping_price' => 3000.00],
            ['name' => 'Peñalolén', 'shipping_price' => 3500.00],
            ['name' => 'Maipú', 'shipping_price' => 4000.00],
            ['name' => 'La Florida', 'shipping_price' => 4000.00],
            ['name' => 'Viña del Mar', 'shipping_price' => 5000.00],
            ['name' => 'Valparaíso', 'shipping_price' => 5000.00],
            ['name' => 'Concepción', 'shipping_price' => 6500.00],
            ['name' => 'La Serena', 'shipping_price' => 6000.00],
            ['name' => 'Temuco', 'shipping_price' => 7000.00],
            ['name' => 'Antofagasta', 'shipping_price' => 8000.00],
            ['name' => 'Punta Arenas', 'shipping_price' => 9500.00],
        ];

        foreach ($communes as $commune) {
            \App\Models\Commune::create($commune);
        }
    }
}
