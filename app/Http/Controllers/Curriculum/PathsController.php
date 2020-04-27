<?php

namespace App\Http\Controllers\Curriculum;

use App\Http\Controllers\AdminBaseController;
use App\Objects\PathCategory;
use App\Objects\PromptPath;
use App\Objects\PromptSegment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PathsController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(function ($request, $next) {
            $this->nav = 'paths';
            return $next($request);
        });
    }

    public function missingPath($type)
    {
        Log::debug("PathsController:missingPath");
        $this->message = "A path needs to be selected to view $type.";
        return $this->getPaths();
    }

    // user selects existing path to work on or to create a new path
    public function getPaths()
    {
        $user = Auth::user();
        $paths = $user->getPaths();
        $paths['new'] = 'Create a new path';

        return $this->adminView('curriculum/path/index', [
            'options' => $paths,
            'title' => 'Learning Paths',
            'nav' => 'paths'
        ]);
    }

    public function postPaths(Request $request)
    {
        $user = Auth::user();
        $pathId = $request->input('path');
        if ($pathId == 'new') {
            $title = "New Path";
            $path = new PromptPath();
            $view = 'new';
        } else {
            try {
                $path = $user->prompt_paths()->where('prompt_path_id', $pathId)->firstOrFail();
                $this->pathId = $pathId;
                $write = $path->pivot->write_access;
            } catch (\Exception $e) {
                Log::debug($e);
                $path = PromptPath::find($pathId);
                $write = false;
            }
            $title = $path->path_title;
            $view = $write ? 'edit' : 'view';
        }
        $difficulties = array_keys($path->getDifficulties());
        $difficulties = array_combine($difficulties, $difficulties);
        return $this->adminView('curriculum/path/'.$view, [
            'path' => $path,
            'title' => $title,
            'difficulties' => $difficulties
        ]);
    }

    public function viewPath(Request $request, $pathId)
    {
        $path = PromptPath::find($pathId);
        $title = $path->path_title;
        return $this->adminView('curriculum/path/view', [
            'path' => $path,
            'title' => $title
        ]);
    }

    public function createOrUpdatePath(Request $request, $pathId = 0)
    {
        $user = Auth::user();
        if ($pathId == 0) {
            $path = new PromptPath();
        } else {
            $path = PromptPath::findOrFail($pathId);
        }
        try {
            Log::debug('path_category: '.$request->input('path_category'));
            $category = PathCategory::findOrFail($request->input('path_category'));
        } catch(\Exception $e) {
            Log::debug($e);
            return $this->getPaths();
        }
        $path->category()->associate($category);
        $path->created_by()->associate($user);
        $path->path_title = $request->input('path_title');
        $path->path_category = $category->name;
        $path->path_thesis = $request->input('path_thesis');
        $path->path_difficulty = $request->input('path_difficulty');
        $path->path_title = $request->input('path_title');

        $create = $request->input();
        $tags = [];
        foreach ($create as $label => $input) {
            if (strpos($label, 'tag_') !== false) {
                $tags[] = $input;
            }
            if ($label == 'new_tags') {
                $tags = array_unique(array_merge($tags, explode(',', $input)));
            }
        }
        $path->setTags($tags);
        $path->save();
        $path->editors()->attach($user, ['write_access' => true]);

        return redirect()->action('Curriculum\PromptsController@getPrompts', [
            'pathId' => $path->id
        ]);
    }
}
