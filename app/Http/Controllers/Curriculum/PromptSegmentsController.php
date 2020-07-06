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
        $this->middleware(function (Request $request, $next) {
            $destination = $request->url();
            if (strpos($destination, '/segments/new') !== false) {
                Log::debug("making a new segment record");
                Log::debug($request);
                return $next($request);
            }
            $this->segmentId = (int) $request->route('segmentId');
            Log::debug(__CLASS__.": segmentId ".$this->segmentId);
            $this->segment = PromptSegment::findOrFail($this->segmentId);
            $user = Auth::user();
            $path = $this->segment->prompt->prompt_path;
            if (!$path->hasAccess($user)) {
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

    public function newSegment(Request $request)
    {
        $required = [ 'segment_title', 'accessory_type', 'prompt_id' ];
        foreach ($required as $require) {
            if (!$request->has($require)) {
                Log::debug("segment form submitted at least partially empty ($require)");
                return false;
            }
        }
        $prompt_id = $request->input('prompt_id');
        $prompt = Prompt::findOrFail($prompt_id);
        $exists = $prompt->prompt_segments()->where('segment_title', $request->input('segment_title'))->first();
        if (!empty($exists)) {
            $this->message = "Not saved! This prompt already has a segment titled \"" .
                $request->input('segment_title0') .
                "\".";
            return false;
        }
        Log::debug("attempting to save new segment titled \"" . $request->input('segment_title') . ".");
        $order = $prompt->prompt_segments_count + 1;
        Log::debug("New order: $order");
        $segment = new PromptSegment([
            'segment_title' => $request->input('segment_title'),
            'prompt_segment_order' => $order,
            'accessory_type' => $request->input('accessory_type')
        ]);
        $prompt->prompt_segments()->save($segment);
        $segment->save();
        return $this->editSegment($request, $segment->id);
    }

    public function editSegment(Request $request, $segmentId)
    {
        $segment = PromptSegment::find($segmentId);
        $fillable = $segment->fillable;
        foreach ($fillable as $attribute) {
            if ($request->has($attribute)) {
                $segment->$attribute = $request->input($attribute);
            }
        }
        $segment->save();
        $this->message = "Prompt segment " . $segment->segment_title . " saved.";
        $prompt = $segment->prompt;
        $path = $prompt->prompt_path;

        Log::debug(__METHOD__);

        return $this->adminView('curriculum/segment/edit', [
            'path' => $path,
            'segment' => $segment,
            'accessory' => $segment->getAccessory(),
            'index' => $segment->prompt_segment_order,
            'last' => $segment->prompt_segment_order == $prompt->prompt_segments_count
        ]);
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
        $segment = PromptSegment::find($this->segmentId);
        Log::debug(__METHOD__.': '.$segment->segment_title);
        $was = $segment->prompt_segment_order;
        $new = $was - 1;
        $result = $segment->updatePromptSegmentOrder($new);
        $title = $segment->segment_title;
        if ($result) {
            $this->message = "Segment $title moved earlier from $was to $new";
        } else {
            $this->message = "Unable to move segment $title earlier from $was to $new";
        }
        Log::debug($this->message);
        $segment = PromptSegment::find($this->segmentId);
        $prompt = $segment->prompt;
        $segments = $prompt->ordered_segments;
        return $this->adminView('curriculum/segment/all', [
            'segments' => $segments,
            'path' => $this->path,
            'prompt_id' => $prompt->id
        ]);
    }

    public function downSegment(Request $request, $segmentId)
    {
        $segment = PromptSegment::find($segmentId);
        Log::debug(__METHOD__.': '.$segment->segment_title);
        $was = $segment->prompt_segment_order;
        $new = $was + 1;
        $result = $segment->updatePromptSegmentOrder($new);
        $title = $segment->segment_title;
        if ($result) {
            $this->message = "Segment $title moved later from position $was to $new";
        } else {
            $this->message = "Unable to move segment $title later from position $was to $new";
        }
        Log::debug($this->message);
        $segment = PromptSegment::find($this->segmentId);
        $prompt = $segment->prompt;
        $path = $prompt->prompt_path;
        $segments = $prompt->ordered_segments;
        return $this->adminView('curriculum/segment/all', [
            'segments' => $segments,
            'path' => $path,
            'prompt_id' => $prompt->id
        ]);
    }
}
