<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            ['name' => 'Webpay Plus', 'code' => 'webpay', 'logo' => 'http://127.0.0.1:8000/images/webpay.svg', 'is_active' => true],
            ['name' => 'Mercado Pago', 'code' => 'mercado_pago', 'logo' => 'http://127.0.0.1:8000/images/mercadopago.svg', 'is_active' => true],
            ['name' => 'Khipu', 'code' => 'khipu', 'logo' => 'http://127.0.0.1:8000/images/khipu.svg', 'is_active' => true],
        ];

        foreach ($methods as $method) {
            PaymentMethod::create($method);
        }
    }
}
