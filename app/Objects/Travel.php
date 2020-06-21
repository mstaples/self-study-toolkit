<?php

namespace App\Objects;

use App\Traits\PathTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Travel extends Model
{
    use PathTrait;
    use SoftDeletes;

    protected $fillable = [ 'operator_id', 'path_id', 'completed', 'completed_on', 'level', 'notebook' ];
    //
    protected $attributes = [
        'completed' => false
    ];

    public function prompt_path()
    {
        return $this->belongsTo('App\Objects\PromptPath');
    }

    public function operator()
    {
        return $this->belongsTo('App\Objects\User');
    }

    public function learnings()
    {
        return $this->belongsToMany('App\Objects\Learnings');
    }

    public function hasKnowledge($name)
    {
        $has = $this->prompt_path->knowledges()->where('name', $name)->first();

        return empty($has) ? false : true;
    }
}
