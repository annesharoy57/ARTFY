<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'profile_photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'membership_expires_at' => 'datetime',
        ];
    }

    public function artistApplication(): HasOne
    {
        return $this->hasOne(ArtistMembershipApplication::class);
    }

    public function artistProfile(): HasOne
    {
        return $this->hasOne(ArtistProfile::class);
    }

    public function artworks(): HasMany
    {
        return $this->hasMany(Artwork::class);
    }

    public function likedArtworks(): BelongsToMany
    {
        return $this->belongsToMany(Artwork::class, 'artwork_likes')
            ->withTimestamps();
    }

    public function savedArtworks(): BelongsToMany
    {
        return $this->belongsToMany(Artwork::class, 'artwork_favorites')
            ->withTimestamps();
    }

    public function artworkComments(): HasMany
    {
        return $this->hasMany(ArtworkComment::class);
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'artist_follows',
            'artist_id',
            'follower_id'
        )->withTimestamps();
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'artist_follows',
            'follower_id',
            'artist_id'
        )->withTimestamps();
    }

    public function exhibitionApplications(): HasMany
    {
        return $this->hasMany(ExhibitionApplication::class);
    }

    public function artistWalletTransactions(): HasMany
    {
        return $this->hasMany(ArtistWalletTransaction::class);
    }

    /**
     * Use the custom notification model to generate UUIDs.
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(
            ArtfyDatabaseNotificationModel::class,
            'notifiable'
        )->latest();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isArtist(): bool
    {
        return $this->role === 'artist';
    }

    public function isArtLover(): bool
    {
        return $this->role === 'art_lover';
    }

    public function isCoAdmin(): bool
    {
        return $this->role === 'co_admin';
    }
}
