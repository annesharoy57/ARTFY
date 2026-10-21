<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        // Order information
        'order_number',
        'buyer_id',
        'artwork_id',
        'artist_id',
        'artwork_title',

        // Artwork price and commission
        'price',
        'commission_rate',
        'commission_amount',
        'artist_amount',

        // Delivery pricing
        'delivery_distance_km',
        'delivery_fee',
        'total_amount',
        'shipping_address',

        // Payment information
        'payment_method',
        'sender_number',
        'transaction_id',
        'payment_status',
        'order_status',
        'verified_by',
        'verified_at',
        'rejection_reason',

        // SSLCOMMERZ
        'gateway_session_key',
        'gateway_val_id',

        // Fulfillment and delivery tracking
        'fulfillment_status',
        'tracking_number',
        'shipped_at',
        'delivered_at',
        'buyer_confirmed_at',

        // Artist payout tracking
        'payout_status',
        'payout_reference',
        'payout_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'artist_amount' => 'decimal:2',

            'delivery_distance_km' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total_amount' => 'decimal:2',

            'verified_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'buyer_confirmed_at' => 'datetime',
            'payout_at' => 'datetime',
        ];
    }

    /**
     * The buyer who placed the order.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * The artist who owns the sold artwork.
     */
    public function artist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'artist_id');
    }

    /**
     * The artwork associated with this order.
     */
    public function artwork(): BelongsTo
    {
        return $this->belongsTo(Artwork::class, 'artwork_id');
    }

    /**
     * The admin who verified the payment, if applicable.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
