<?php

namespace App\Objects\Archetypes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Option extends Model
{
    public $states = [
        'draft', 'review', 'trial', 'live'
    ];
    protected $fillable = [ 'question_id', 'question_text', 'option', 'correct', 'state' ];
    protected $attributes = [
        'state' => 'draft',
        'question_text' => ''
    ];

}
