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

class EditorsController extends AdminBaseController
{

    public function __construct()
    {
        parent::__construct();
        /*
         * Redirect any request that doesn't have the right access or data.
         * Also pull request info into the class that would otherwise be duplicated in each action
         * */
        $this->middleware(function ($request, $next) {
            $this->pathId = (int) $request->route('pathId');
            $this->path = PromptPath::find($this->pathId);
            //Log::debug(__CLASS__.': Path: '.$this->path->path_title);
            if (!$this->path->hasAccess(Auth::user())) {
                $this->message = "Current user doesn't have edit access to the selected path.";
                //Log::debug($this->message);
                return redirect('prompts/view/'.$this->pathId);
            }
            $this->nav = 'editors';
            return $next($request, $this->pathId);
        });
    }

    public function getEditors(Request $request, $pathId)
    {
        $path = PromptPath::find($pathId);
        $user = Auth::user();
        $creator = $path->created_by;
        $created_by_id = $creator ? $creator->id : false;
        if (!$path->hasAccess($user)) {
            $editors = $path->getActiveEditors();
            // view
            return $this->adminView('curriculum/editor/view', [
                'path' => $path,
                'editors' => $editors,
                'created_by_id' => $created_by_id,
                'nav' => 'editors'
            ]);
        }
        $editors = $path->getActiveEditors(true);
        // edit
        $options = [
            'none' => 'None',
            'read' => 'Read',
            'write' => 'Edit'
        ];
        return $this->adminView('curriculum/editor/edit', [
            'path' => $path,
            'editors' => $editors,
            'options' => $options,
            'created_by_id' => $created_by_id,
            'nav' => 'editors'
        ]);
    }

    public function postEditors(Request $request, $pathId)
    {
        Log::debug(__METHOD__);
        $path = PromptPath::find($pathId);
        $user = Auth::user();
        if (!$path->hasAccess($user)) {
            $this->message = "You don't have edit access to the selected path.";
            Log::debug($this->message);
            return $this->getEditors($request, $pathId);
        }
        $create = $request->input();
        $readerIds = [];
        $editorIds = [];
        Log::debug($create);
        foreach ($create as $label => $access) {
            if (strpos($label, 'editor_') !== false) {
                $split = explode('_', $label);
                $id = $split[1];
                if ($access == 'read') {
                    $readerIds[] = $id;
                } elseif ($access == 'write') {
                    $editorIds[] = $id;
                }
            }
        }
        $path->updateEditors($readerIds, $editorIds);
        $this->message = "Editor preferences saved.";
        return redirect()->action('Curriculum\PromptsController@getPrompts', [ 'pathId' => $pathId ]);
    }

    public function getInvite()
    {

    }

    public function postInvite(Request $request)
    {

    }
}
