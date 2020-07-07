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
            return redirect()->action('Curriculum\PromptController@postPrompt', [
                'pathId' => $pathId,
                'promptId' => $promptId
            ]);
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

}
