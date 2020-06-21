<?php

namespace App\Objects;

use Illuminate\Database\Eloquent\Model;

class FeedbackRecord extends Model
{

    protected $fillable = [ 'operator_id', 'author_id', 'feedback_request_id', 'feedback_request', 'answer', 'rating', 'relationship' ];

    public function operator()
    {
        return $this->belongsTo('App/Objects/Operator');
    }
}
