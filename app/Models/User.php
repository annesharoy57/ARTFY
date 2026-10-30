<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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

    public function exhibitionApplications(): HasMany
    {
        return $this->hasMany(ExhibitionApplication::class);
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
}
