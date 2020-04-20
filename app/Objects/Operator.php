<?php

namespace App\Objects;

use Illuminate\Database\Eloquent\Model;

class Operator extends Model
{
    protected $fillable = ['slack_user_id', 'name'];
}
