<?php

namespace App\Http\Controllers\Curriculum;

use App\Http\Controllers\AdminBaseController;
use App\Objects\PathCategory;
use App\Objects\Prompt;
use App\Objects\PromptPath;
use App\Objects\PromptSegment;
use App\Objects\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PromptsController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            $destination = $request->url();
            if (strpos($destination, '/prompts/view/') === false) {
                $parameters = $request->route()->parameters();
                if (array_key_exists('pathId', $parameters)) {
                    $this->pathId = $request->route('pathId');
                    $path = PromptPath::find($this->pathId);
                    if (!empty($path) && !$path->hasAccess($user)) {
                        $this->message = "Current user doesn't have edit access to the selected Path.";
                        return redirect()->action('Curriculum\PromptPathController@getPaths');
                    }
                }
                $hasPaths = $user->prompt_paths_count;
                $hasPrompts = $user->prompts_count;
                if ($hasPaths + $hasPrompts === 0) {
                    $message = "You haven't selected a path or previously authored any prompts, so there aren't any prompts to show right now.";
                    return redirect()
                        ->action('Curriculum\PathsController@getPaths')
                        ->with('message', $message);
                }
            }
            $this->nav = 'prompts';
            return $next($request);
        });
    }

    public function postDemoPrompt($request, $promptId)
    {
        $prompt = Prompt::findOrFail($promptId);
        $path = $prompt->prompt_path;
        $user = Auth::user();
        $operator = $user->operator;
        if (empty($operator)) {
            return $this->adminView('curriculum/path/demo', [
                'title' => 'Connect your operator',
                'nav' => 'paths',
                'path' => $path
            ]);
        }
        $this->message = $operator->startPromptDemo($promptId);
        return $this->editPrompt($request, $path->id, $prompt->id);
    }

    public function viewPrompts($pathId)
    {
        $path = PromptPath::findOrFail($pathId);
        $title = $path->path_title;
        $prompts = Prompt::where('prompt_path_id', $pathId)->get();
        return $this->adminView('curriculum/prompt/view', [
            'prompts' => $prompts,
            'title' => $title . ': Prompts',
            'nav' => 'prompts',
            'path' => $path
        ]);
    }

    // user selects existing prompt to work on or to create a new prompt
    public function getPrompts($pathId = null)
    {
        $this->pathId = $pathId;
        $path = PromptPath::find($pathId);
        $user = Auth::user();
        $heading = '';
        $options = [];
        if (empty($path)) {
            $title = "Authored prompts";
            $prompts = $user->prompts;
            $heading = 'component/heading/path';
        } else {
            $title = $path->path_title;
            $options = [ 'new' => 'Create a new prompt'];
            $prompts = Prompt::where('prompt_path_id', $pathId)->get();
        }
        Log::debug(__METHOD__);
        Log::debug(count($prompts));
        if (count($prompts) == 0 && count($options) == 0 || (!empty($path) && !$path->hasAccess($user))) {
            $this->message = "Select a path to view, edit, or create prompts you haven't already authored.";
            return redirect()->action('Curriculum\PathsController@getPaths');
        }
        foreach ($prompts as $prompt) $options[$prompt->id] = $prompt->prompt_title;
        return $this->adminView('curriculum/prompt/index', [
            'options' => $options,
            'title' => $title . ': Prompts',
            'nav' => 'prompts',
            'heading' => $heading,
            'path' => $path
        ]);
    }

    public function postPrompts(Request $request, $pathId)
    {
        $promptId = $request->input('prompt');
        if ($promptId && $promptId != 'new') {
            Log::debug("postPrompts($pathId, $promptId)");
            return $this->postPrompt($pathId, $promptId);
        }
        $path = PromptPath::findOrFail($pathId);
        $title = $path->path_title . ": New Prompt";
        $prompt = new Prompt();
        return $this->adminView('curriculum/prompt/new', [
            'path' => $path,
            'title' => $title,
            'prompt' => $prompt,
            'nav' => 'segments'
        ]);
    }

    public function postPrompt($pathId, $promptId)
    {
        Log::debug("postPrompt($pathId, $promptId)");
        $path = PromptPath::find($pathId);
        $prompt = Prompt::find($promptId);
        Log::debug("postPrompt called with prompt id $promptId");
        $title = $path->path_title . " Prompt: ". $prompt->prompt_title;
        $segments = $prompt->ordered_segments;
        return $this->adminView('curriculum/prompt/edit', [
            'path' => $path,
            'title' => $title,
            'prompt' => $prompt,
            'segments' => $segments,
            'nav' => 'segments'
        ]);
    }

    public function editPrompt(Request $request, $pathId, $promptId)
    {
        $prompt = Prompt::find($promptId);
        $repeatable = $request->input('repeatable') == 'true' ? true : false;
        $optional = $request->input('optional') == 'true' ? true : false;
        $prompt->prompt_title = $request->input('prompt_title');
        $prompt->repeatable = $repeatable;
        $prompt->optional = $optional;
        $prompt->save();

        switch($request->input('next')) {
            case 'questions':
                return redirect()->action('Curriculum\SamplingQuestionsController@getSamplingQuestions');
            case 'prompts':
                return redirect()->action('Curriculum\PromptsController@getPrompts');
            case 'paths':
                return redirect()->action('Curriculum\PathsController@getPaths');
            case 'stay':
            default:
            $path = PromptPath::findOrFail($pathId);
            $title = $path->path_title . " Prompt: ". $prompt->prompt_title;
            return $this->adminView('curriculum/prompt/edit', [
                'path' => $path,
                'title' => $title,
                'prompt' => $prompt,
                'segments' => $prompt->ordered_segments,
                'nav' => 'prompts'
            ]);
        }
    }

    public function createPrompt(Request $request, $pathId)
    {
        $path = PromptPath::findOrFail($pathId);
        $check = $path->prompts()->where('prompt_title', $request->input('prompt_title'))->first();
        if ($check) {
            $this->message = "This path already has a prompt titled ".$request->input('prompt_title');
            return $this->adminView('curriculum/prompt/new', [
                'path' => $path,
                'title' => $path->path_title . ": New Prompt",
                'prompt' => $check,
                'nav' => 'prompts'
            ]);
        }
        $repeatable = $request->input('repeatable') == 'true' ? true : false;
        $optional = $request->input('optional') == 'true' ? true : false;
        $newPrompt = [
            'prompt_title' => $request->input('prompt_title'),
            'repeatable' => $repeatable,
            'optional' => $optional
        ];
        $prompt = new Prompt($newPrompt);
        $path->prompts()->save($prompt);
        $prompt->save();

        $title = $path->path_title . " Prompt: ". $prompt->prompt_title;
        return $this->adminView('curriculum/prompt/edit', [
            'path' => $path,
            'title' => $title,
            'prompt' => $prompt,
            'segments' => $prompt->ordered_segments,
            'nav' => 'prompts'
        ]);
    }

}
