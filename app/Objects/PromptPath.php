<?php

namespace App\Objects;

use App\Traits\KnowledgableTrait;
use App\Traits\PathTrait;
use App\Traits\EditableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class PromptPath extends Model
{
    use PathTrait;
    use EditableTrait;
    use KnowledgableTrait;
    use SoftDeletes;

    protected $fillable = [ 'state', 'path_level', 'path_category', 'path_title', 'steps','repeatable', 'path_thesis', 'created_by_id' ];
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

    public function ordered_prompts()
    {
        return $this->prompts()->orderBy('prompt_path_step', 'asc');
    }

    public function knowledges()
    {
        return $this->belongsToMany('App\Objects\Knowledge', 'knowledges_paths', 'path_id', 'knowledge_id');
    }

    public function travels()
    {
        return $this->hasMany('App\Objects\Travel');
    }

    public function updateEditors($readerIds, $editorIds)
    {
        $creator = $this->created_by;
        $created_by_id = $creator->id;
        $all = $this->editors;
        foreach ($all as $editor) {
            Log::debug("updateEditors: ".$editor->name);
            if ($created_by_id == $editor->id) {
                continue;
            }
            if (!in_array($editor->id, $readerIds) &&
                !in_array($editor->id, $editorIds)) {
                Log::debug("no access: ".$editor->name);
                $this->editors()->detach($editor);
                $editor->save();
                $this->save();
                continue;
            }
            if (in_array($editor->id, $readerIds)) {
                Log::debug("read access: ".$editor->name);
                $editor->pivot->write_access = false;
                $editor->pivot->save();
                $editor->save();
                $this->save();
                unset($readerIds[array_search($editor->id, $readerIds)]);
                continue;
            }
            Log::debug("write access: ".$editor->name);
            $editor->pivot->write_access = true;
            $editor->pivot->save();
            $editor->save();
            $this->save();
            unset($editorIds[array_search($editor->id, $editorIds)]);
            continue;
        }
        if (!empty($readerIds)) {
            foreach ($readerIds as $id) {
                Log::debug("updateEditors: ".$editor->name);
                $editor = User::find($id);
                $this->editors()->attach($editor);
                $editor->pivot->write_access = false;
                $editor->pivot->save();
                $editor->save();
            }
        }
        if (!empty($editorIds)) {
            foreach ($editorIds as $id) {
                $editor = User::find($id);
                $this->editors()->attach($editor);
                $editor->pivot->write_access = true;
                $editor->pivot->save();
                $editor->save();
            }
        }
        $this->save();
        return;
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

    public function getSteps()
    {
        $count = count($this->prompts);
        $steps = array_fill(1, $count, '');
        $preamble = 'Make this Prompt #';
        $ownedPreable = 'Keep this Prompt #';
        foreach ($this->prompts as $prompt) {
            if ($prompt->prompt_path_step == $this->prompt_path_step) {
                $title = $ownedPreable . $this->prompt_path_step;
            } else {
                $title = $preamble . $prompt->prompt_path_step;
            }
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
                    $steps[$step] = $preamble . $step;
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

    public function getActiveEditors($includeAll = false)
    {
        $editors = $this->editors()->select(['users.id', 'users.name'])->where('users.name', '!=', null)->get();
        $active = [];
        foreach ($editors as $editor) {
            $selected = $editor->pivot->write_access ? 'write' : 'read';
            if (array_key_exists($editor->id, $active)) {
                $this->editors()->detach($editor);
                $this->save();
            }
            $active[$editor->id] = [ 'name' => $editor->name, 'selected' => $selected ];
        }
        if (!$includeAll) {
            return $active;
        }
        $all = User::select([ 'id', 'name' ])->where('name', '!=', null)->get();
        $options = [];
        foreach ($all as $each) {
            if (!array_key_exists($each->id, $active)) {
                $options[$each->id] = [ 'name' => $each->name, 'selected' => 'none' ];
            }
        }
        return $active + $options;
    }
}
