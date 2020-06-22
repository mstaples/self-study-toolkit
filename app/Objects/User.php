<?php

namespace App\Objects;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'admin', 'active'
    ];

    protected $attributes = [
        'admin' => false,
        'active' => true
    ];

    protected $withCount = [
        'authored_paths', 'authored_prompts', 'authored_sampling_questions', 'authored_feedback_requests', 'prompts', 'prompt_paths', 'sampling_questions'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function authored_paths()
    {
        return $this->hasMany('App\Objects\PromptPath', 'created_by_id');
    }

    public function authored_prompts()
    {
        return $this->hasMany('App\Objects\Prompt', 'created_by_id');
    }

    public function authored_sampling_questions()
    {
        return $this->hasMany('App\Objects\SamplingQuestion', 'created_by_id');
    }

    public function authored_feedback_requests()
    {
        return $this->hasMany('App\Objects\FeedbackRequest', 'created_by_id');
    }

    public function prompts()
    {
        return $this->belongsToMany('App\Objects\Prompt')
            ->using('App\Objects\PromptUser')
            ->withPivot([ 'write_access' ]);
    }

    public function prompt_paths()
    {
        return $this->belongsToMany('App\Objects\PromptPath')
                ->using('App\Objects\PathPromptUser')
                ->withPivot([ 'write_access' ]);
    }

    public function sampling_questions()
    {
        return $this->belongsToMany('App\Objects\SamplingQuestion', 'sampling_questions_users')
            ->using('App\Objects\SamplingQuestionUser')
            ->withPivot([ 'write_access' ]);
    }

    public function getQuestionsByKnowledge($knowledge)
    {
        Log::debug("getQuestionsByKnowledge($knowledge)");
        $questions = $this->sampling_questions()
            ->whereHas('knowledges', function (Builder $query) use ($knowledge) {
                $query->where('name', $knowledge);
            })->get();

        $set = [];
        foreach ($questions as $question) {
            $note = $question->pivot->write_access ?'':' (read only)';
            $set[$question->id] = $note . ' ' . $question->question;
        }
        return $set;
    }

    public function getPaths()
    {
        $options = [];
        $paths = $this->prompt_paths;
        foreach ($paths as $path) {
            $note = $path->pivot->write_access ?'':' (read only)';
            $options[$path->id] = $path->path_title . $note;
        }
        $note = ' (read only)';
        $seeds = PromptPath::where(['created_by_id' => null])->get();
        foreach ($seeds as $path) {
            $options[$path->id] = $path->path_title . $note;
        }
        return $options;
    }

    public function getAuthoredPaths()
    {
        $authored = $this->authored_paths;
        $options = [];
        foreach ($authored as $path) {
            $prompt_path_id = $path->prompt_path_id;
            $write_access = $this->prompt_paths()->find($prompt_path_id)->pivot->write_access;
            $note = $write_access ?'':' (read only)';
            $options[$path->id] = $path->path_title . $note;
        }
        $note = ' (read only)';
        $seeds = PromptPath::where(['created_by_id' => null])->get();
        foreach ($seeds as $path) {
            $options[$path->id] = $path->path_title . $note;
        }
        return $options;
    }

    public function getAuthoredPrompts()
    {
        $authored = $this->authored_prompts;
        $options = [];
        foreach ($authored as $path) {
            $prompt_path_id = $path->prompt_path_id;
            $write_access = $this->prompt_paths()->find($prompt_path_id)->pivot->write_access;
            $note = $write_access ?'':' (read only)';
            $options[$path->id] = $path->path_title . $note;
        }
        $note = ' (read only)';
        $seeds = PromptPath::where(['created_by_id' => null])->get();
        foreach ($seeds as $path) {
            $options[$path->id] = $path->path_title . $note;
        }
        return $options;
    }

    public function getAuthoredSamplingQuestions()
    {
        $authored = $this->authored_sampling_questions;
        $options = [];
        foreach ($authored as $path) {
            $prompt_path_id = $path->prompt_path_id;
            $write_access = $this->prompt_paths()->find($prompt_path_id)->pivot->write_access;
            $note = $write_access ?'':' (read only)';
            $options[$path->id] = $path->path_title . $note;
        }
        $note = ' (read only)';
        $seeds = PromptPath::where(['created_by_id' => null])->get();
        foreach ($seeds as $path) {
            $options[$path->id] = $path->path_title . $note;
        }
        return $options;
    }
    public function getAuthoredFeedbackRequests()
    {
        $authored = $this->authored_feedback_requests;
        $options = [];
        foreach ($authored as $path) {
            $prompt_path_id = $path->prompt_path_id;
            $write_access = $this->prompt_paths()->find($prompt_path_id)->pivot->write_access;
            $note = $write_access ?'':' (read only)';
            $options[$path->id] = $path->path_title . $note;
        }
        $note = ' (read only)';
        $seeds = PromptPath::where(['created_by_id' => null])->get();
        foreach ($seeds as $path) {
            $options[$path->id] = $path->path_title . $note;
        }
        return $options;
    }
}
