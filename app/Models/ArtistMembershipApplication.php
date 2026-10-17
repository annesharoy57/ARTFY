<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtistMembershipApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'artist_name',
        'biography',
        'art_category',
        'art_categories',
        'portfolio_url',
        'status',
        'payment_status',
        'membership_fee',
        'payment_method',
        'payment_reference',
        'payment_submitted_at',
        'payment_verified_at',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'art_categories' => 'array',
            'membership_fee' => 'integer',
            'payment_submitted_at' => 'datetime',
            'payment_verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}