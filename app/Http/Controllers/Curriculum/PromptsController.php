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
            $destination = $request->url();
            if (strpos($destination, '/prompts/view/') === false) {
                $this->pathId = $request->route('pathId');
                $path = PromptPath::findOrFail($this->pathId);
                if (!$path->hasAccess(Auth::user())) {
                    $this->message = "Current user doesn't have edit access to the selected Path.";
                    return $this->viewPrompts($path->id);
                }
            }
            $this->nav = 'prompts';
            return $next($request);
        });
    }

    public function viewPrompts($pathId)
    {
        $path = PromptPath::findOrFail($pathId);
        $title = $path->path_title;
        $prompts = Prompt::where('prompt_path_id', $pathId)->get();
        return $this->adminView('curriculum/prompt/view', [
            'prompts' => $prompts,
            'title' => $title . ': Prompts',
            'nav' => 'prompts'
        ]);
    }

    // user selects existing prompt to work on or to create a new prompt
    public function getPrompts($pathId)
    {
        $this->pathId = $pathId;
        $path = PromptPath::findOrFail($pathId);
        $title = $path->path_title;
        $options = [ 'new' => 'Create a new prompt'];
        $prompts = Prompt::where('prompt_path_id', $pathId)->get();
        if ($prompts) {
            foreach ($prompts as $prompt) $options[$prompt->id] = $prompt->prompt_title;
        }
        return $this->adminView('curriculum/prompt/index', [
            'options' => $options,
            'title' => $title . ': Prompts',
            'nav' => 'prompts'
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
        $segments = $prompt->prompt_segments;
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
        $count = $prompt->prompt_segments_count;
        if ($count == 0) {
            Log::debug("editPrompt calling newPromptSegmentFromRequest for first prompt segment");
            $this->newPromptSegmentFromRequest($request, $prompt, ++$count);
        } else {
            Log::debug("editPrompt calling newPromptSegmentFromRequest for prompt w $count existing segments");
            $this->newPromptSegmentsFromRequest($request, $prompt, ++$count);
        }

        $repeatable = $request->input('repeatable') == 'true' ? true : false;
        $prompt->prompt_title = $request->input('prompt_title');
        $prompt->repeatable = $repeatable;
        $prompt->save();
        if ($request->input('next') != 'add') return $this->getSamplingQuestions($pathId);

        $path = PromptPath::findOrFail($pathId);
        $title = $path->path_title . " Prompt: ". $prompt->prompt_title;
        return $this->adminView('curriculum/prompt/edit', [
            'path' => $path,
            'title' => $title,
            'prompt' => $prompt,
            'segments' => $prompt->prompt_segments,
            'nav' => 'segments'
        ]);
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
                'nav' => 'segments'
            ]);
        }
        $repeatable = $request->input('repeatable') == 'true' ? true : false;
        $newPrompt = [
            'prompt_title' => $request->input('prompt_title'),
            'repeatable' => $repeatable
        ];
        $prompt = new Prompt($newPrompt);
        $path->prompts()->save($prompt);
        $prompt->save();
        $prompt->setPromptStep($request->input('prompt_step'));
        $prompt = $this->newPromptSegmentFromRequest($request, $prompt);

        if ($request->input('next') == 'add') {
            $title = $path->path_title . " Prompt: ". $prompt->prompt_title;
            return $this->adminView('curriculum/prompt/edit', [
                'path' => $path,
                'title' => $title,
                'prompt' => $prompt,
                'segments' => $prompt->prompt_segments,
                'nav' => 'segments'
            ]);
        }

        return $this->getSamplingQuestions($pathId);
    }

}
