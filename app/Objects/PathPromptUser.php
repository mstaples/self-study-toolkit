<?php

namespace App\Objects;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PathPromptUser extends Pivot
{

    protected $fillable = [ 'user_id', 'prompt_path_id', 'write_access' ];
    //
    protected $attributes = [
        'write_access' => false
    ];

}
