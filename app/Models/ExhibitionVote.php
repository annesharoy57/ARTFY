<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExhibitionVote extends Model
{
    protected $fillable = [
        'exhibition_application_id',
        'user_id',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(ExhibitionApplication::class, 'exhibition_application_id');
    }

    public function voter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
