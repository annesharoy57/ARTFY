<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
