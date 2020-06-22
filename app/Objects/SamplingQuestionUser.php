<?php

namespace App\Objects;

use Illuminate\Database\Eloquent\Relations\Pivot;

class SamplingQuestionUser extends Pivot
{

    protected $fillable = [ 'user_id', 'sampling_question_id', 'write_access' ];
    //
    protected $attributes = [
        'write_access' => false
    ];

}
