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

    public function getEditors()
    {
        $this->message = "Feature not yet available.";
        $this->message_role = "info";
        return $this->adminView('message');
    }

    public function postEditors(Request $request, $pathId)
    {

    }

    public function getInvite()
    {

    }

    public function postInvite(Request $request)
    {

    }
}
