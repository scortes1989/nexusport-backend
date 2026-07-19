<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = \App\Models\Category::all()->keyBy('slug');

        $products = [
            [
                'category_slug' => 'chaquetas',
                'name' => 'Aura Windrunner',
                'description' => 'Cortavientos técnico ultraligero y plegable, resistente al agua y viento.',
                'long_description' => 'El Aura Windrunner redefine la protección climática para deportistas. Construido con poliéster ripstop 100% reciclado con tratamiento repelente al agua (DWR), es capaz de soportar lluvias ligeras y ráfagas de viento fuertes. Su diseño inteligente permite plegarlo por completo dentro de su propio bolsillo para guardarlo fácilmente.',
                'price' => 89.99,
                'gradient' => 'from-orange-500 to-red-600',
                'rating' => 4.80,
                'reviews_count' => 124,
                'specs' => [
                    ['label' => 'Material', 'value' => 'Poliéster 100% Reciclado con DWR'],
                    ['label' => 'Peso', 'value' => '115 gramos (Talla M)'],
                    ['label' => 'Visibilidad', 'value' => 'Detalles reflectantes de 360 grados'],
                    ['label' => 'Corte', 'value' => 'Ajuste deportivo (Active Fit)'],
                ],
                'featured' => true,
                'image_url' => 'https://images.unsplash.com/photo-1508214751196-bcfd4ca60f91?auto=format&fit=crop&w=600&q=80',
                'sizes' => [
                    ['size' => 'S', 'stock' => 5],
                    ['size' => 'M', 'stock' => 8],
                    ['size' => 'L', 'stock' => 0],
                    ['size' => 'XL', 'stock' => 2],
                ],
            ],
            [
                'category_slug' => 'calzado',
                'name' => 'Sonic Zoom Runner',
                'description' => 'Zapatillas de running de alto rendimiento con amortiguación reactiva ultraligera.',
                'long_description' => 'Diseñadas para corredores que buscan batir sus récords personales. Las Sonic Zoom Runner incorporan nuestra tecnología de espuma ZoomTech de doble densidad en la entresuela, proporcionando un retorno de energía del 85% en cada zancada. El empeine de malla técnica sin costuras garantiza máxima ventilación y un ajuste perfecto tipo calcetín.',
                'price' => 139.99,
                'gradient' => 'from-blue-500 to-teal-500',
                'rating' => 4.90,
                'reviews_count' => 88,
                'specs' => [
                    ['label' => 'Entresuela', 'value' => 'Espuma reactiva ZoomTech'],
                    ['label' => 'Drop', 'value' => '8 mm'],
                    ['label' => 'Suela', 'value' => 'Goma de alta fricción antideslizante'],
                    ['label' => 'Tipo de Pisada', 'value' => 'Neutra'],
                ],
                'featured' => true,
                'image_url' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=600&q=80',
                'sizes' => [
                    ['size' => '39', 'stock' => 3],
                    ['size' => '40', 'stock' => 0],
                    ['size' => '41', 'stock' => 8],
                    ['size' => '42', 'stock' => 6],
                    ['size' => '43', 'stock' => 5],
                    ['size' => '44', 'stock' => 0],
                ],
            ],
            [
                'category_slug' => 'pantalones',
                'name' => 'Helix Compression Tights',
                'description' => 'Calzas de compresión graduada para soporte muscular y rápida recuperación.',
                'long_description' => 'Las calzas Helix Compression ofrecen soporte avanzado a los grupos musculares mayores (cuádriceps, isquiotibiales y pantorrillas), reduciendo la oscilación muscular y previniendo la fatiga. Su tejido elástico en 4 direcciones absorbe la humedad y cuenta con tratamiento antibacteriano para mantenerte fresco.',
                'price' => 69.99,
                'gradient' => 'from-indigo-600 to-blue-700',
                'rating' => 4.70,
                'reviews_count' => 205,
                'specs' => [
                    ['label' => 'Compresión', 'value' => 'Compresión médica graduada (20-25 mmHg)'],
                    ['label' => 'Tejido', 'value' => '82% Nylon, 18% Elastano'],
                    ['label' => 'Ajuste', 'value' => 'Cintura alta con cordón interior plano'],
                    ['label' => 'Bolsillos', 'value' => 'Bolsillo trasero con cierre para llaves/móvil'],
                ],
                'featured' => true,
                'image_url' => 'https://images.unsplash.com/photo-1539185441755-769473a23570?auto=format&fit=crop&w=600&q=80',
                'sizes' => [
                    ['size' => 'S', 'stock' => 3],
                    ['size' => 'M', 'stock' => 5],
                    ['size' => 'L', 'stock' => 0],
                ],
            ],
            [
                'category_slug' => 'poleras',
                'name' => 'KeyForge Thermo Tee',
                'description' => 'Polera térmica de manga larga y secado rápido para climas fríos.',
                'long_description' => 'Entrena al aire libre sin importar el clima. La KeyForge Thermo Tee cuenta con un tejido de microfibra cepillada en su interior que atrapa el calor corporal y libera la humedad al exterior. Su estructura elástica y costuras planas minimizan el roce y permiten total libertad de movimiento en tus entrenamientos.',
                'price' => 45.00,
                'gradient' => 'from-amber-500 to-orange-600',
                'rating' => 4.60,
                'reviews_count' => 63,
                'specs' => [
                    ['label' => 'Tecnología', 'value' => 'Interior cepillado ThermoWarm'],
                    ['label' => 'Costuras', 'value' => 'Planas (Flatlock) anti-roce'],
                    ['label' => 'Protección Solar', 'value' => 'UPF 50+ certificado'],
                    ['label' => 'Detalles', 'value' => 'Orificios para los pulgares en puños'],
                ],
                'featured' => false,
                'image_url' => 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?auto=format&fit=crop&w=600&q=80',
                'sizes' => [
                    ['size' => 'S', 'stock' => 10],
                    ['size' => 'M', 'stock' => 12],
                    ['size' => 'L', 'stock' => 8],
                ],
            ],
            [
                'category_slug' => 'accesorios',
                'name' => 'Lumen Hydration Vest',
                'description' => 'Chaleco de hidratación ergonómico y ultra transpirable para Trail Running.',
                'long_description' => 'Diseñado en colaboración con corredores de montaña de élite. El chaleco Lumen cuenta con una capacidad de carga de 8 litros distribuida en 9 bolsillos de acceso rápido. Incluye dos botellas flexibles (soft flasks) de 500 ml cada una y un compartimento trasero compatible con bolsa de agua de hasta 2 litros.',
                'price' => 99.99,
                'gradient' => 'from-lime-400 to-emerald-600',
                'rating' => 4.80,
                'reviews_count' => 42,
                'specs' => [
                    ['label' => 'Capacidad', 'value' => '8 Litros de almacenamiento'],
                    ['label' => 'Hidratación', 'value' => 'Incluye 2x Soft Flasks de 500ml'],
                    ['label' => 'Material', 'value' => 'Malla elástica transpirable 3D Airmesh'],
                    ['label' => 'Ajuste', 'value' => 'Doble correa de esternón de tensión rápida'],
                ],
                'featured' => false,
                'image_url' => 'https://images.unsplash.com/photo-1551854838-212c50b4c184?auto=format&fit=crop&w=600&q=80',
                'sizes' => [
                    ['size' => 'Única', 'stock' => 5],
                ],
            ],
            [
                'category_slug' => 'accesorios',
                'name' => 'Apex Grip Socks',
                'description' => 'Calcetines deportivos antideslizantes de compresión media para máxima agilidad.',
                'long_description' => 'Evita resbalones dentro del calzado y previene ampollas. Los calcetines Apex Grip incorporan almohadillas de silicona ergonómicas en la planta del pie que aumentan la tracción. Con soporte de arco de compresión media y costuras invisibles en la puntera para un confort insuperable.',
                'price' => 19.99,
                'gradient' => 'from-pink-500 to-rose-600',
                'rating' => 4.50,
                'reviews_count' => 112,
                'specs' => [
                    ['label' => 'Antideslizante', 'value' => 'Plancha de agarre de silicona médica'],
                    ['label' => 'Composición', 'value' => '75% Algodón peinado, 20% Nylon, 5% Spandex'],
                    ['label' => 'Acolchado', 'value' => 'Puntera y talón reforzados'],
                    ['label' => 'Diseño', 'value' => 'Estructura anatómica izquierda/derecha'],
                ],
                'featured' => false,
                'image_url' => 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?auto=format&fit=crop&w=600&q=80',
                'sizes' => [
                    ['size' => 'Única', 'stock' => 45],
                ],
            ],
        ];

        foreach ($products as $prod) {
            $catSlug = $prod['category_slug'];
            unset($prod['category_slug']);
            
            $sizes = $prod['sizes'];
            unset($prod['sizes']);
            
            $prod['category_id'] = $categories[$catSlug]->id;
            $product = \App\Models\Product::create($prod);
            
            $product->sizes()->createMany($sizes);
        }
    }
}
