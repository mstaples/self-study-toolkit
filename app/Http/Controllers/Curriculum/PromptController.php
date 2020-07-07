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

class PromptController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            $destination = $request->url();
            if (strpos($destination, '/prompt/view/') === true) {
                $this->nav = 'prompts';
                return $next($request);
            }
            $parameters = $request->route()->parameters();
            if (array_key_exists('pathId', $parameters)) {
                $this->pathId = $request->route('pathId');
                $path = PromptPath::find($this->pathId);
                if (!empty($path) && !$path->hasAccess($user)) {
                    $this->message = "Current user doesn't have edit access to the selected Path.";
                    return redirect()->action('Curriculum\PromptPathController@viewPath', [
                        'pathId' => $this->pathId
                    ]);
                }
            } elseif (array_key_exists('promptId', $parameters)) {
                $prompt = Prompt::find($parameters['promptId']);
                $path = $prompt->path;
                if (!empty($path) && !$path->hasAccess($user)) {
                    $this->message = "Current user doesn't have edit access to the selected Path.";
                    return redirect()->action('Curriculum\PromptPathController@viewPath', [
                        'pathId' => $path->id
                    ]);
                }
            }
            Log::debug($destination);
            Log::debug($parameters);
            $this->nav = 'prompts';
            return $next($request);
        });
    }

    public function anyDemoPrompt(Request $request, $promptId)
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

    public function postPrompt(Request $request, $pathId, $promptId)
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
