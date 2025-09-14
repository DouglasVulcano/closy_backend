<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Campaign extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'start_date',
        'end_date',
        'status',
        'details'
    ];

    /**
     * Get the user that owns the campaign
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
