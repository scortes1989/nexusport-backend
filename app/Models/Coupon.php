<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'discount_value' => 'float',
        'min_purchase_amount' => 'float',
        'max_discount_amount' => 'float',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function checkValidity(float $subtotal): ?string
    {
        if ($this->trashed()) {
            return 'El cupón no está disponible.';
        }

        $now = now();
        if ($now->lt($this->start_date)) {
            return 'El cupón aún no está vigente.';
        }

        if ($now->gt($this->end_date)) {
            return 'El cupón ha expirado.';
        }

        if ($this->usage_limit !== null && $this->orders()->count() >= $this->usage_limit) {
            return 'El cupón ha alcanzado su límite máximo de usos.';
        }

        if ($subtotal < $this->min_purchase_amount) {
            $formattedMin = number_format($this->min_purchase_amount, 0, ',', '.');
            return "El monto mínimo de compra para utilizar este cupón es de \${$formattedMin}.";
        }

        return null;
    }

    public function calculateDiscount(float $subtotal, float $shippingCost): float
    {
        $discount = 0.0;

        if ($this->type === 'product') {
            if ($this->discount_type === 'percentage') {
                $discount = $subtotal * ($this->discount_value / 100.0);
                if ($this->max_discount_amount !== null && $discount > $this->max_discount_amount) {
                    $discount = $this->max_discount_amount;
                }
                $discount = min($discount, $subtotal);
            } else {
                $discount = min((float) $this->discount_value, $subtotal);
            }
        } elseif ($this->type === 'shipping') {
            if ($this->discount_type === 'percentage') {
                $discount = $shippingCost * ($this->discount_value / 100.0);
                $discount = min($discount, $shippingCost);
            } else {
                $discount = min((float) $this->discount_value, $shippingCost);
            }
        }

        return round($discount, 2);
    }
}
