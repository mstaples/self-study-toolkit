<?php

namespace App\Http\Controllers;

use App\Objects\Prompt;
use App\Objects\PromptSegment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;

class AdminBaseController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $message = '';
    public $message_role = 'info';
    public $promptId = '';
    public $pathId = '';
    public $segmentId = '';
    public $questionId = '';
    public $nav = '';

    public function __construct()
    {
        $this->message = '';
        $this->middleware('auth');
    }

    public function adminView($view, $data = [])
    {
        $data['message'] = $this->message;
        $data['message_role'] = $this->message_role;
        $selection_ids = [ 'pathId', 'promptId', 'segmentId', 'questionId', 'nav' ];
        foreach ($selection_ids as $id) {
            if (!array_key_exists($id, $data)) $data[$id] = $this->$id;
        }
        Log::debug($this->message);
        return view($view, $data);
    }

    public function newPromptSegmentsFromRequest(Request $request, $prompt, $count)
    {
        for ($i = 1; $i <= $count; $i++) {
            if ($i == $count) $i = ''; // account for final form data always being the new segment option
            $prompt = $this->newPromptSegmentFromRequest($request, $prompt, $i);
            if (!$prompt) break;
            if ($i == '') break;
        }
        return $prompt;
    }

    public function newPromptSegmentFromRequest(Request $request, Prompt $prompt, $index = '')
    {
        if (!$request->has('segment_title'.$index)
            || strlen($request->input('segment_title'.$index)) < 1) {
            Log::debug("segment form submitted at least partially empty ($index)");
            return false;
        }
        if ($request->has('segment_id'.$index)) {
            Log::debug("newPromptSegmentFromRequest has segment_id $index, finding record");
            $segment = PromptSegment::find($request->input('segment_id'.$index));
        } else {
            $check = $prompt->prompt_segments()->where('segment_title', $request->input('segment_title'))->first();
            if (!empty($check)) {
                $this->message = "Not saved! This prompt already has a segment titled \"" . $request->input('segment_title'.$index) . "\".";
                Log::debug("Prompt segments for double submitted or title submitted for multiple segments.");
                return false;
            }
            Log::debug("attempting to save new segment titled \"" . $request->input('segment_title') . ".");
            $order = $prompt->prompt_segments_count + 1;
            Log::debug("New order: $order");
            $segment = new PromptSegment([
                'segment_title' => $request->input('segment_title'.$index),
                'prompt_segment_order' => $order
            ]);
            $prompt->prompt_segments()->save($segment);
            $segment->save();
        }
        $updateSegment = [
            'segment_title' => $request->input('segment_title'.$index),
            'segment_text' => $request->input('segment_text'.$index),
            'imageUrl' => $request->input('segment_imageUrl'.$index),
            'url' => $request->input('segment_url'.$index)
        ];
        $segment->update($updateSegment);
        $options = explode(',', $request->input('segment_options'.$index));
        $correct = explode(',', $request->input('segment_answers'.$index));
        $answers = [];
        foreach ($options as $option) {
            $mark = in_array($option, $correct) ? true : false;
            $answers[$option] = $mark;
        }
        $accessory = $segment->segment_accessory;
        if (!empty($options)) $accessory['options'] = $answers;
        if ($request->input('segment_accessory_type'.$index) != 'none') {
            $accessory['type'] = $request->input('segment_accessory_type'.$index);
        }
        $accessory['text']['text'] = $request->input('segment_accessory_text'.$index);
        $segment->segment_accessory = $accessory;
        $segment->save();

        return $prompt;
    }
}
