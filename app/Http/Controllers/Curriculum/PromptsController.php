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
    // user selects existing prompt to work on or to create a new prompt
    public function getPrompts($pathId)
    {
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
            'pathId' => $pathId
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
            'nav' => 'paths'
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
            'orderOptions' => $prompt->getSegmentOrderOptions(),
            'nav' => 'paths'
        ]);
    }

    public function editPrompt(Request $request, $pathId, $promptId)
    {
        $prompt = Prompt::withCount('prompt_segments')->where('id',$promptId)->first();
        $count = count($prompt->prompt_segments);
        if ($count == 0) {
            Log::debug("editPrompt calling newPromptSegmentFromRequest for first prompt segment");
            $this->newPromptSegmentFromRequest($request, $prompt, ++$count);
        } else {
            Log::debug("editPrompt calling newPromptSegmentFromRequest for prompt w $count existing segments");
            $this->newPromptSegmentsFromRequest($request, $prompt, ++$count);
        }

        $prompt->prompt_title = $request->input('prompt_title');
        $prompt->repeatable = $request->input('repeatable');
        $prompt->setPromptStep($request->input('prompt_step'));
        $prompt->save();
        if ($request->input('next') != 'add') return $this->getSamplingQuestions($pathId);

        $path = PromptPath::findOrFail($pathId);
        $title = $path->path_title . " Prompt: ". $prompt->prompt_title;
        return $this->adminView('curriculum/prompt/edit', [
            'path' => $path,
            'title' => $title,
            'prompt' => $prompt,
            'segments' => $prompt->prompt_segments,
            'orderOptions' => $prompt->getSegmentOrderOptions(),
            'nav' => 'paths'
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
                'nav' => 'paths'
            ]);
        }
        $newPrompt = [
            'prompt_title' => $request->input('prompt_title'),
            'repeatable' => $request->input('repeatable')
        ];
        $prompt = new Prompt($newPrompt);
        $path->prompts()->save($prompt);
        $prompt->save();
        $prompt->setPromptStep($request->input('prompt_step'));
        $this->newPromptSegmentFromRequest($request, $prompt);

        if ($request->input('next') == 'add') {
            $title = $path->path_title . " Prompt: ". $prompt->prompt_title;
            return $this->adminView('curriculum/prompt/edit', [
                'path' => $path,
                'title' => $title,
                'prompt' => $prompt,
                'segments' => $prompt->prompt_segments,
                'nav' => 'paths'
            ]);
        }

        return $this->getSamplingQuestions($pathId);
    }

    public function viewPrompts($pathId)
    {
        $path = PromptPath::findOrFail($pathId);
        $title = $path->path_title;
        $prompts = Prompt::where('prompt_path_id', $pathId)->get();
        return $this->adminView('curriculum/prompts/view', [
            'prompts' => $prompts,
            'title' => $title . ': Prompts',
            'nav' => 'paths'
        ]);
    }

    public function newPromptSegmentsFromRequest(Request $request, $prompt, $count)
    {
        for ($i = 1; $i <= $count; $i++) {
            if ($i == $count) $i = ''; // account for final form data always being the new segment option
            $response = $this->newPromptSegmentFromRequest($request, $prompt, $i);
            if (!$response) break;
        }
        return;
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
            $check = $prompt->prompt_segments()->where('segment_title', $request->input('segment_title'.$index))->first();
            if (!empty($check)) {
                $this->message = "Not saved! This prompt already has a segment titled \"" . $request->input('segment_title'.$index) . "\".";
                Log::debug("Prompt segments for double submitted or title submitted for multiple segments.");
                return false;
            }
            Log::debug("attempting to save new segment titled \"" . $request->input('segment_title'.$index) . ".");
            $segment = new PromptSegment([
                'segment_title' => $request->input('segment_title'.$index),
                'prompt_segment_order' => $prompt->prompt_segments_count + 1
            ]);
            $prompt->prompt_segments()->save($segment);
            $segment->save();
        }
        $newSegment = [
            'segment_title' => $request->input('segment_title'.$index),
            'segment_text' => $request->input('segment_text'.$index),
            'imageUrl' => $request->input('segment_imageUrl'.$index),
            'url' => $request->input('segment_url'.$index)
        ];
        $segment->update($newSegment);
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
        $order = $request->input('prompt_segment_order'.$index);
        if ($order > 0 && $order <= count($segment->prompt->prompt_segments)) {
            $segment->setPromptSegmentOrder($request->input('prompt_segment_order'.$index));
        } else {
            $title = $segment->segment_title;
            Log::debug("prompt segment $title has invalid prompt_segment_order value: $order");
        }
        $segment->save();

        return true;
    }

}
