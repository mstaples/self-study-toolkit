<?php

namespace App\Objects;

use Illuminate\Database\Eloquent\Model;

class PromptResponse extends Model
{
    //
    protected $fillable = ['prompt_id', 'prompt_title', 'response'];

    public function operator()
    {
        return $this->belongsTo('App/Objects/Operator');
    }
}
