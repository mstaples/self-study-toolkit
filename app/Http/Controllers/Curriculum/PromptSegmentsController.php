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

class PromptSegmentsController extends AdminBaseController
{
    public $path;
    public $segmentId;
    public $segment;

    public function __construct()
    {
        parent::__construct();
        /*
         * Redirect any request that doesn't have the right access or data.
         * Also pull request info into the class that would otherwise be duplicated in each action
         * */
        $this->middleware(function ($request, $next) {
            $this->segmentId = (int) $request->route('segmentId');
            $this->segment = PromptSegment::findOrFail($this->segmentId);
            $user = Auth::user();
            if (!$this->segment->path->hasAccess($user)) {
                $this->message = "Current user doesn't have edit access to the selected Path.";
                return $this->viewPrompts($this->segment->path->id);
            }
            $prompt = $this->segment->prompt;
            $this->path = $prompt->prompt_path;
            $this->nav = 'segments';
            return $next($request, $this->segmentId);
        });
    }

    public function getSegments(Request $request)
    {
        $user = Auth::user();
        $segments = $user->authored_segments;
    }

    public function editSegment(Request $request, $segmentId)
    {
        $fillable = $this->segment->fillable;
        foreach ($fillable as $attribute) {
            Log::debug(__METHOD__.': update segment '.$attribute);
            $this->segment->$attribute = $request->input($attribute);
        }
        $this->segment->save();
        $this->message = "Prompt segment \"$this->segment->segment_title\" saved.";

        return $this->adminView('curriculum/segment/edit', [ 'path' => $this->path ]);
    }

    public function deleteSegment(Request $request, $segmentId)
    {
        Log::debug(__CLASS__.': '.__METHOD__.': '.$this->segment->segment_title);
        $title = $this->segment->segment_title;
        $this->segment->delete();

        $this->message = "Deleted segment \"$title\"";
        return $this->adminView('component/message');
    }

    public function upSegment(Request $request, $segmentId)
    {
        Log::debug(__CLASS__.': '.__METHOD__.': '.$this->segment->segment_title);
        $result = $this->segment->moveOrderEarlier();
        $title = $this->segment->segment_title;
        if ($result) {
            $this->message = "Segment $title moved earlier";
        } else {
            $this->message = "Unable to move segment $title earlier";
        }
        Log::debug($this->message);
        $segment = PromptSegment::find($segmentId);
        $prompt = $segment->prompt;
        $segments = $prompt->ordered_segments;
        return $this->adminView('curriculum/segment/all', [ 'segments' => $segments, 'path' => $this->path ]);
    }

    public function downSegment(Request $request, $segmentId)
    {
        Log::debug(__CLASS__.': '.__METHOD__.': '.$this->segment->segment_title);
        $result = $this->segment->moveOrderLater();
        $title = $this->segment->segment_title;
        if ($result) {
            $this->message = "Segment $title moved later";
        } else {
            $this->message = "Unable to move segment $title later";
        }
        Log::debug($this->message);
        $segment = PromptSegment::find($segmentId);
        $prompt = $segment->prompt;
        $segments = $prompt->ordered_segments;
        return $this->adminView('curriculum/segment/all', [ 'segments' => $segments, 'path' => $this->path ]);
    }
}
