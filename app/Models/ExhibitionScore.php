<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExhibitionScore extends Model
{
    protected $fillable = [
        'exhibition_application_id',
        'co_admin_id',
        'score',
    ];

    protected function casts(): array
    {
        return ['score' => 'integer'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(ExhibitionApplication::class, 'exhibition_application_id');
    }

    public function coAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'co_admin_id');
    }
}
