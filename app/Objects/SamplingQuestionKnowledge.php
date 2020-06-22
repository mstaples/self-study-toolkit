<?php

namespace App\Objects;

use Illuminate\Database\Eloquent\Relations\Pivot;

class SamplingQuestionKnowledge extends Pivot
{
    protected $fillable = [ 'question_id', 'knowledge_id' ];

    protected $table = 'knowledges_questions';
}
