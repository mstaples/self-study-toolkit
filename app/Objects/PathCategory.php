<?php

namespace App\Objects;

use Illuminate\Database\Eloquent\Model;

class PathCategory extends Model
{
    protected $fillable = [ 'state', 'name', 'min', 'max', 'span', 'per', 'description' ];

    protected $attributes = [
        'state' => 'review'
    ];

    public function promptPaths()
    {
        return $this->hasMany('App\Objects\PromptPath');
    }
}
