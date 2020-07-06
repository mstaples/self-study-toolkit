<?php

namespace App\Http\Controllers\Curriculum;

use App\Http\Controllers\AdminBaseController;
use App\Objects\SamplingOption;
use App\Objects\SamplingQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SamplingOptionsController extends AdminBaseController
{
    public $questionId;
    public $question;
    public $message = '';
    public $message_role = 'info';

    public function __construct()
    {
        parent::__construct();
        /*
         * Redirect any request that doesn't have the right access or data.
         * Also pull request info into the class that would otherwise be duplicated in each action
         * */
        $this->middleware(function ($request, $next) {
            $this->questionId = (int) $request->route('questionId');
            $this->question = SamplingQuestion::find($this->questionId);
            $user = Auth::user();
            $has = $user->sampling_questions()->where('sampling_question_id', $this->questionId)->first();
            if (empty($has)) {
                $this->message = "Current user doesn't have edit access to the selected question.";
                Log::debug($this->message);
                return redirect()->action('Curriculum\SamplingQuestionsController@adminView', [
                    'request' => $request
                ]);
            }
            $this->nav = 'questions';
            if (strpos($request->url, 'edit') !== false
                || strpos($request->url, 'delete') !== false) {
                return $next($request, $this->questionId, $request->route('optionId'));
            }
            return $next($request, $this->questionId);
        });
    }

    public function allSamplingOptions(Request $request, $questionId)
    {
        return $this->adminView('curriculum/option/all', [
            'options' => $this->question->options,
            'questionId' => $this->questionId
        ]);
    }

    public function createSamplingOption(Request $request, $questionId)
    {
        $question = SamplingQuestion::find($questionId);
        $check = $question->options()->where('option', $request->input('option'))->first();
        if ($check) {
            $this->message = "This question already has an option reading: ".$request->input('option');
            return $this->adminView('curriculum/option/new', [
                'questionId' => $this->questionId
            ]);
        }
        $correct = $request->input('correct') == 'true' ? true : false;
        $newQuestion = [
            'option' => $request->input('option'),
            'question_text' => $this->question->question,
            'correct' => $correct
        ];
        $option = new SamplingOption($newQuestion);
        $this->question->options()->save($option);
        $option->save();

        return $this->allSamplingOptions($request, $questionId);
    }

    public function editSamplingOption(Request $request, $questionId, $optionId)
    {
        $option = SamplingOption::find($optionId);
        $text = $request->input('option');
        Log::debug(__METHOD__.': edit option: '.$option->option);
        $check = $this->question->options()
            ->where('option', $text)
            ->where('id', '!=', $optionId)
            ->first();
        if ($check) {
            $this->message = "This question already has an option that reads: $text";
            $this->message_role = "warning";
            Log::debug($this->message);
            return $this->adminView('curriculum/option/edit', [
                'option' => $option
            ]);
        }
        $correct = $request->input('correct') == 'true' ? true : false;
        Log::debug(__METHOD__.': option? '.$text);
        Log::debug(__METHOD__.': correct? '.$correct);
        $option->option = $request->input('option');
        $option->correct = $correct;
        $option->save();
        $this->message = "Option \"$text\" (correct? ".$request->input('correct').") saved.";
        $this->message_role = 'success';
        return $this->adminView('curriculum/option/edit', [
            'option' => $option,
        ]);
    }


    public function deleteSamplingOption(Request $request, $questionId, int $optionId)
    {
        $option = SamplingOption::find($optionId);
        $text = $option->option;
        Log::debug(__METHOD__.': delete option: '.$text);
        $option->delete();
        $this->message = "Option \"$text\"  deleted.";
        $this->message_role = 'success';
        return $this->adminView('component/message');
    }
}
