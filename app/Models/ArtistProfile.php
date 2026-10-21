<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtistProfile extends Model
{
    protected $fillable = [
        'user_id',
        'artist_name',
        'profile_photo',
        'cover_photo',
        'bio',
        'art_category',
        'location',
        'latitude',
        'longitude',
        'portfolio_url',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
