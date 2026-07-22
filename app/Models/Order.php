<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $guarded = [];

    protected $casts = [
        'estimated_dispatch_date' => 'date',
        'estimated_delivery_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->code)) {
                do {
                    $code = 'ORD-' . strtoupper(Str::random(8));
                } while (static::where('code', $code)->exists());
                $order->code = $code;
            }
        });
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
