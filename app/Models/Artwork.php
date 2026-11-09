<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Artwork extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'image',
        'category',
        'medium',
        'price',
        'status',
        'moderation_status',
        'is_sold',
        'artwork_type',
        'digital_file_path',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_sold' => 'boolean',
        ];
    }

    /**
     * The artist who created this artwork.
     */
    public function artist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function likes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'artwork_likes')
            ->withTimestamps();
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'artwork_favorites')
            ->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ArtworkComment::class);
    }
}
