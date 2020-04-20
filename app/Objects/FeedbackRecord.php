<?php

namespace App\Objects;

use Illuminate\Database\Eloquent\Model;

class FeedbackRecord extends Model
{
    protected $fillable = ['feedback_request_id', 'feedback_request', 'response'];
    //
    public function operator()
    {
        return $this->belongsTo('App/Objects/Operator');
    }
}
