<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Exhibition extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'subtitle',
        'description',
        'registration_opens_at',
        'registration_closes_at',
        'starts_at',
        'ends_at',
        'max_artists',
        'registration_fee',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'registration_opens_at' => 'datetime',
            'registration_closes_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'max_artists' => 'integer',
            'registration_fee' => 'decimal:2',
        ];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(ExhibitionApplication::class);
    }

    public function settlement(): HasOne
    {
        return $this->hasOne(ExhibitionSettlement::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(ArtistWalletTransaction::class);
    }

    public function registrationIsOpen(): bool
    {
        $opensAt = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $this->registration_opens_at->format('Y-m-d H:i:s'),
            'Asia/Dhaka'
        );
        $closesAt = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $this->registration_closes_at->format('Y-m-d H:i:s'),
            'Asia/Dhaka'
        );
        $now = Carbon::now('Asia/Dhaka');

        return $now->greaterThanOrEqualTo($opensAt)
            && $now->lessThanOrEqualTo($closesAt);
    }

    public function visitIsOpen(): bool
    {
        $now = Carbon::now('Asia/Dhaka');

        return $this->status === 'published'
            && $now->greaterThanOrEqualTo($this->visitStartsAt())
            && $now->lessThanOrEqualTo($this->visitEndsAt());
    }

    public function visitHasStarted(): bool
    {
        return Carbon::now('Asia/Dhaka')
            ->greaterThanOrEqualTo($this->visitStartsAt());
    }

    public function visitHasEnded(): bool
    {
        return Carbon::now('Asia/Dhaka')
            ->greaterThan($this->visitEndsAt());
    }

    private function visitStartsAt(): Carbon
    {
        return Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $this->starts_at->format('Y-m-d H:i:s'),
            'Asia/Dhaka'
        );
    }

    private function visitEndsAt(): Carbon
    {
        return Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $this->ends_at->format('Y-m-d H:i:s'),
            'Asia/Dhaka'
        );
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
