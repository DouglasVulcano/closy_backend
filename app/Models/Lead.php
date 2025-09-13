<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'campaign_id',
        'name',
        'email',
        'celular',
        'status',
        'question_responses'
    ];
}
