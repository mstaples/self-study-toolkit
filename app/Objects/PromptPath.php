<?php

namespace App\Objects;

use App\Traits\PathTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromptPath extends Model
{
    use PathTrait;
    use SoftDeletes;

    protected $fillable = [ 'state', 'path_level', 'path_category', 'path_title', 'steps','repeatable', 'path_thesis', 'created_by' ];
    //
    protected $attributes = [
        'state' => 'review',
        'steps' => 0
    ];

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

    public function knowledges()
    {
        return $this->belongsToMany('App\Objects\Knowledge', 'knowledges_paths', 'path_id', 'knowledge_id');
    }

    public function getKnowledgeNames()
    {
        $knowledges = $this->knowledges;
        $names = [];
        foreach ($knowledges as $knowledge) {
            $names[$knowledge->name] = $knowledge->name;
        }
        return $names;
    }

    public function hasKnowledge($name)
    {
        $has = $this->knowledges()->where('name',$name)->first();
        return empty($has) ? false : true;
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
