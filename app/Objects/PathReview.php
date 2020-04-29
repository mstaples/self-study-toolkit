<?php

namespace App\Objects;

use Illuminate\Database\Eloquent\Model;

class PathReview extends Model
{
    protected $fillable = [ 'state', 'name', 'min', 'max', 'span', 'per', 'description' ];

    protected $attributes = [
        'state' => 'review'
    ];

    public function prompt_paths()
    {
        return $this->hasMany('App\Objects\PromptPath');
    }
}
