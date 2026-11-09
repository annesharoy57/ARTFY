<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArtistWalletTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'exhibition_id',
        'exhibition_application_id',
        'type',
        'amount',
        'description',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function artist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function exhibition(): BelongsTo
    {
        return $this->belongsTo(Exhibition::class);
    }
}
