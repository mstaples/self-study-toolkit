<?php

namespace App\Http\Controllers\Curriculum;

use App\Http\Controllers\AdminBaseController;
use App\Objects\PathCategory;
use App\Objects\Prompt;
use App\Objects\PromptPath;
use App\Objects\PromptSegment;
use App\Objects\SamplingQuestion;
use App\Objects\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SamplingQuestionsController extends AdminBaseController
{
    public $pathId;
    public $path;

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
            $this->nav = 'questions';
            if (strpos($request->url, 'edit') !== false) {
                return $next($request, $this->pathId, $request->route('questionId'));
            }
            return $next($request, $this->pathId);
        });
    }

    public function getSamplingQuestions(Request $request, $pathId)
    {
        $questions = $this->path->sampling_questions;
        foreach ($questions as $question) {
            $options[$question->id] = $question->question;
        }
        $options['new'] = 'Create a new sampling question';
        return $this->adminView('curriculum/question/index', [
            'options' => $options,
            'title' => 'Sampling Questions',
            'nav' => 'questions'
        ]);
    }

    public function postSamplingQuestions(Request $request)
    {
        $title ="Sampling Question";
        if ($request->input('question') == 'new') {
            $question = new SamplingQuestion();
            return $this->adminView('curriculum/question/new', [
                'title' => 'New sampling question',
                'depths' => $question->getDepths()
            ]);
        }
        try {
            $question = SamplingQuestion::findOrFail('id', $request->input('question'));
        } catch (\Exception $e) {
            $this->message = "Couldn't locate the requested sampling question.";
            return $this->getSamplingQuestions($request);
        }
        return $this->adminView('curriculum/question/edit', [
            'title' => $title . ': Edit sampling question',
            'question' => $question
        ]);
    }

    public function createSamplingQuestion(Request $request)
    {
        $check = SamplingQuestion::where('question', $request->input('question'))->first();
        if ($check) {
            $this->message = "This sampling question already exists.";
            $question = new SamplingQuestion();
            $depths = $question->getDepths();
            return $this->adminView('curriculum/question/new', [
                'title' => "New Sampling Question",
                'depth' => $depths,
                'nav' => 'questions'
            ]);
        }
        $newQuestion = [
            'question' => $request->input('question'),
            'depth' => $request->input('depth')
        ];
        $question = new SamplingQuestion($newQuestion);
        $question->save();

        $title = "Sampling Question";
        return $this->adminView('curriculum/question/edit', [
            'question' => $question,
            'title' => $title,
            'nav' => 'questions'
        ]);
    }

    public function editSamplingQuestion(Request $request, $pathId, $questionId)
    {
        Log::debug(__METHOD__.": ".__LINE__);
        $question = SamplingQuestion::find($questionId);
        $question->question = $request->input('question');
        $question->depth = $request->input('depth');
        $question->save();

        $this->message = "Question saved.";
        return $this->adminView('curriculum/question/edit', [
            'question' => $question,
            'question_id' => $questionId,
            'nav' => 'questions'
        ]);
    }
}
