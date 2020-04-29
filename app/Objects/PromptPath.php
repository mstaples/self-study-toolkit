<?php

namespace App\Objects;

use App\Traits\PathTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromptPath extends Model
{
    use PathTrait;
    use SoftDeletes;

    protected $fillable = [ 'state', 'path_difficulty', 'path_category', 'path_title', 'steps', 'tags', 'repeatable', 'path_thesis', 'created_by' ];
    //
    protected $attributes = [
        'state' => 'review',
        'steps' => 0
    ];

    protected $cast = [
        'tags' => 'array'
    ];

    public function setTags($tags)
    {
        if (is_array($tags)) {
            $tags = json_encode($tags, true);
        }
        $this->tags = $tags;
    }

    public function category()
    {
        return $this->belongsTo('App\Objects\PathCategory', 'path_category_id');
    }

    public function created_by()
    {
        return $this->belongsTo('App\Objects\User', 'created_by_id');
    }

    public function editors()
    {
        return $this->belongsToMany('App\Objects\User')
            ->using('App\Objects\PathPromptUser')
            ->withPivot([ 'write_access' ]);
    }

    public function prompts()
    {
        return $this->hasMany('App\Objects\Prompt');
    }

    public function sampling_questions()
    {
        return $this->hasMany('App\Objects\SamplingQuestion');
    }

    public function hasTag($tag)
    {
        return in_array($tag, json_decode($this->tags, true));
    }

    public function hasAccess(User $user)
    {
        if ($user == $this->created_by) return true;
        $find = $this->editors()->where([
            'user_id' => $user->id
        ])->first();

        if ($find) return true;

        return false;
    }

    public function getSteps()
    {
        $count = count($this->prompts);
        $steps = array_fill(1, $count, '');
        foreach ($this->prompts as $prompt) {
            $title = $prompt->prompt_path_step . '. (' . $prompt->prompt_title . ')';
            if (array_key_exists($prompt->prompt_path_step, $steps)
                && $steps[$prompt->prompt_path_step] == '') {
                $steps[$prompt->prompt_path_step] = $title;
                continue;
            }
            $title = substr($title, 1);
            $lastStep = 0;
            foreach ($steps as $step => $label) {
                $lastStep = $step;
                if ($label === '') {
                    $steps[$step] = $step . $title;
                    $prompt->prompt_path_step = $step;
                    $prompt->save();
                    continue 2;
                }
            }
            $steps[++$lastStep] = $lastStep . $title;
            $prompt->prompt_path_step = $lastStep;
            $prompt->save();
        }
        if (empty($steps)) {
            $steps[1] = "1 (First prompt)";
        }
        return $steps;
    }
}
