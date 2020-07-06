<?php

namespace App\Http\Controllers\Curriculum;

use App\Http\Controllers\AdminBaseController;
use App\Objects\Knowledge;
use App\Objects\Operator;
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

    public function getDemoPath(Request $request, $pathId)
    {
        Log::debug(__METHOD__);
        $path = PromptPath::findOrFail($pathId);
        $user = Auth::user();
        $operator = $user->operator;
        Log::debug($user);
        if (empty($operator)) {
            return $this->adminView('curriculum/path/demo', [
                'title' => 'Connect your operator',
                'nav' => 'paths',
                'path' => $path
            ]);
        }
        $this->message = $operator->startPathDemo($pathId);
        return redirect()->action('Curriculum\PromptsController@viewPrompts', [
            'pathId' => $pathId
        ]);
    }

    public function postDemoPath(Request $request, $pathId)
    {
        Log::debug(__METHOD__);
        $path = PromptPath::findOrFail($pathId);
        $user = Auth::user();
        $operator = $user->operator;
        if (!empty($operator)) {
            return $this->getDemoPath($request, $pathId);
        }
        $slack_user_id = $request->input('slack_user_id');
        $operator = Operator::where('slack_user_id',$slack_user_id)->first();
        if (empty($operator)) {
            if (strlen($slack_user_id) > 0) {
                $this->message = "There's no app operator with that slack user id in the system. Have you opted into the app after creating your slack account?";
            }
            return $this->getDemoPath($request, $pathId);
        }
        $code = md5($user->id);
        $code = substr($code,8,2).'-'.substr($code,5,3).'-'.substr($code,2,2);
        $operator->code = $code;
        $operator->save();
        $operator->sendSlackConnectUserForm($user->id);

        $this->message = "Invitation to connect slack account sent! Check the app home in your Slack workspace.";

        return $this->adminView('curriculum/path/connect', [
            'title' => 'Connect your operator',
            'code' => $operator->code,
            'slack_user_id' => $slack_user_id,
            'nav' => 'paths',
            'path' => $path
        ]);
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
            $knowledges = $path->allKnowledges();
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
            if ($write) {
                $view = 'edit';
                $knowledges = $path->allKnowledges(false, true);
            } else {
                $view = 'view';
                $knowledges = $path->pathKnowledges();
            }
        }
        $levels = array_keys($path->getLevels());
        $levels = array_combine($levels, $levels);
        Log::debug(__METHOD__);
        Log::debug($path);
        return $this->adminView('curriculum/path/'.$view, [
            'path' => $path,
            'title' => $title,
            'levels' => $levels,
            'knowledges' => $knowledges
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
            if (!$path->hasAccess($user)) {
                $this->message = "Current user doesn't have edit access to the requested path.";
                return $this->getPaths();
            }
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
        $path->path_level = $request->input('path_level');
        $path->path_title = $request->input('path_title');

        $create = $request->input();
        $knowledges = [];
        foreach ($create as $label => $input) {
            if (strpos($label, 'knowledge_') !== false) {
                $knowledges[] = $input;
            }
        }
        $path->save();
        $path->editors()->attach($user, ['write_access' => true]);
        foreach ($knowledges as $knowledge) {
            $know = Knowledge::where(['name' => strtolower($knowledge)])->first();
            $know->save();
            $has = $path->knowledges()->where('knowledge_id', $know->id)->first();
            if (empty($has)) {
                $path->knowledges()->attach($know);
            }
        }

        return redirect()->action('Curriculum\PromptsController@getPrompts', [
            'pathId' => $path->id
        ]);
    }
}
