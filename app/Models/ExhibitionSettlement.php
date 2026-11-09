<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExhibitionSettlement extends Model
{
    protected $fillable = [
        'exhibition_id',
        'settled_by',
        'total_fees',
        'admin_fund_amount',
        'unallocated_prize_amount',
        'settled_at',
    ];

    protected function casts(): array
    {
        return [
            'total_fees' => 'decimal:2',
            'admin_fund_amount' => 'decimal:2',
            'unallocated_prize_amount' => 'decimal:2',
            'settled_at' => 'datetime',
        ];
    }

    public function exhibition(): BelongsTo
    {
        return $this->belongsTo(Exhibition::class);
    }
}
