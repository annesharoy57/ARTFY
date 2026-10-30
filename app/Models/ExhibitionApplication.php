<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExhibitionApplication extends Model
{
    public const PENDING_PAYMENT_HOLD_MINUTES = 30;

    protected $fillable = [
        'exhibition_id',
        'user_id',
        'registration_fee',
        'status',
        'payment_status',
        'payment_method',
        'payment_reference',
        'payment_submitted_at',
        'payment_verified_at',
        'artwork_title',
        'artwork_description',
        'artwork_price',
        'artwork_image',
    ];

    protected function casts(): array
    {
        return [
            'registration_fee' => 'decimal:2',
            'payment_submitted_at' => 'datetime',
            'payment_verified_at' => 'datetime',
            'artwork_price' => 'decimal:2',
        ];
    }

    public function exhibition(): BelongsTo
    {
        return $this->belongsTo(Exhibition::class);
    }

    public function artist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeActivePaymentReservation(Builder $query): Builder
    {
        return $query
            ->where('status', 'pending')
            ->where('payment_status', 'pending')
            ->whereNotNull('payment_submitted_at')
            ->where(
                'payment_submitted_at',
                '>=',
                now()->subMinutes(self::PENDING_PAYMENT_HOLD_MINUTES)
            );
    }

    public function scopeReservesArtistPlace(Builder $query): Builder
    {
        return $query
            ->where('payment_status', 'verified')
            ->whereIn('status', ['awaiting_submission', 'confirmed']);
    }

    public function isActivePaymentReservation(): bool
    {
        return $this->status === 'pending'
            && $this->payment_status === 'pending'
            && $this->payment_submitted_at !== null
            && $this->payment_submitted_at->greaterThanOrEqualTo(
                now()->subMinutes(self::PENDING_PAYMENT_HOLD_MINUTES)
            );
    }

    public function reservesArtistPlace(): bool
    {
        return $this->payment_status === 'verified'
            && in_array(
                $this->status,
                ['awaiting_submission', 'confirmed'],
                true
            );
    }
}
