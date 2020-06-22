<?php

namespace App\Objects;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PromptUser extends Pivot
{

    protected $fillable = [ 'user_id', 'prompt_id', 'write_access' ];
    //
    protected $attributes = [
        'write_access' => false
    ];

}
