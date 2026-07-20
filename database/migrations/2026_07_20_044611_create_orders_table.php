<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('shipping_address');
            $table->foreignId('commune_id')->constrained();
            $table->decimal('shipping_cost', 8, 2);
            $table->decimal('subtotal', 8, 2);
            $table->decimal('total', 8, 2);
            $table->string('status')->default('paid');
            $table->foreignId('payment_method_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
