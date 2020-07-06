<?php

namespace App\Http\Controllers\Curriculum;

use App\Http\Controllers\AdminBaseController;
use App\Objects\Knowledge;
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
            $user = Auth::user();
            $destination = $request->url();
            if (strpos($destination, '/questions/view/') === false) {
                $parameters = $request->route()->parameters();
                if (array_key_exists('questionId', $parameters)) {
                    $this->questionId = $request->route('questionId');
                    $question = SamplingQuestion::find($this->questionId);
                    if (!empty($question) && !$question->hasAccess($user)) {
                        $this->message = "Current user doesn't have edit access to the selected Question.";
                        return $this->getKnowledges();
                    }
                }
            }
            $this->nav = 'questions';
            return $next($request);
        });
    }

    public function createKnowledge(Request $request)
    {
        if ($request->has('knowledge') && $request->input('knowledge') == 'new') {
            return $this->adminView('curriculum/knowledge/new', [
                'title' => "New Knowledge",
                'nav' => 'knowledges'
            ]);
        }
        $required = [ 'name' ];
        $save = true;
        foreach ($required as $require) {
            if (!$request->has($require)) {
                Log::debug(__METHOD__.": called without required form data: ".$require);
                $save = false;
                $this->message = "New knowledge not saved (form error).";
                break;
            }
        }
        $check = Knowledge::where('name', $request->input('name'))->first();
        if ($check) {
            $this->message = "This knowledge already exists.";
            $save = false;
        }
        if (strlen($request->input('name') < 3)) {
            Log::debug(__METHOD__.": called with an unexpectedly short 'name' value: ".$request->input('name'));
            $save = false;
            $this->message = "New knowledge not saved (form error).";
        }
        if (!$save) {
            return $this->adminView('curriculum/knowledge/new', [
                'title' => "New Knowledge",
                'nav' => 'knowledges'
            ]);
        }
        $newKnowledge = [
            'name' => $request->input('name'),
            'prerequisite' => false
        ];
        if ($request->has('description') && strlen($request->input('description')) > 3) {
            $newKnowledge['description'] = $request->input("description");
        }
        $knowledge = new Knowledge($newKnowledge);
        $knowledge->save();

        return $this->getSamplingQuestions($request, $knowledge->name);
    }

    public function getKnowledges(Request $request)
    {
        $knowledges = Knowledge::all();
        $options = [];

        foreach ($knowledges as $knowledge)
        {
            $options[$knowledge->id] = $knowledge->name;
        }
        $options['new'] = "Create a new knowledge";
        return $this->adminView('curriculum/knowledge/index', [
            'options' => $options,
            'title' => 'Knowledges: select or create',
            'nav' => 'knowledges'
        ]);
    }

    public function postKnowledges(Request $request)
    {
        $know = $request->has('knowledge');
        if (!$know) return $this->getKnowledges($request);

        $know = $request->input('knowledge');
        if ($know == 'new') {
            return $this->createKnowledge($request);
        }

        Log::debug("postKnowledges()->input $know");
        $knowledge = Knowledge::find($know);
        return $this->getSamplingQuestions($request, $knowledge->name);
    }

    public function getSamplingQuestions(Request $request, $knowledge)
    {
        $user = Auth::user();
        Knowledge::where('name', $knowledge)->firstOrFail();
        $questions = $user->getQuestionsByKnowledge($knowledge, true);
        $questions['new'] = "Create a new question";
        return $this->adminView('curriculum/question/index', [
            'options' => $questions,
            'title' => ucfirst($knowledge) . ' Questions',
            'nav' => 'questions'
        ]);
    }

    public function postSamplingQuestions(Request $request)
    {
        $title ="Sampling Question";
        $user = Auth::user();
        $input = $request->input('question');
        if (strpos($input,'new') !== false) {
            $knowledge = str_replace('new_', '', $input);
            $path = new PromptPath();
            return $this->adminView('curriculum/question/new', [
                'title' => "New sampling question",
                'knowledges' => $path->allKnowledges(),
                'depths' => $path->getDepths(),
                'knowledge' => $knowledge
            ]);
        }
        $question = SamplingQuestion::find($request->input('question'));
        if (empty($question)) {
            $this->message = "Couldn't locate the requested sampling question.";
            return $this->getKnowledges($request);
        }
        $has = $user->sampling_questions()
            ->where('sampling_question_id', $request->input('question'))
            ->first();
        if (empty($has) || !$has->write_access) {
            return $this->adminView('curriculum/question/view', [
                'title' => $title . ' (Read only)',
                'question' => $question,
                'knowledges' => $question->getCurrentKnowledges()
            ]);
        }
        return $this->adminView('curriculum/question/edit', [
            'title' => $title . ': Edit sampling question',
            'question' => $question,
            'knowledges' => $question->getCurrentKnowledges()
        ]);
    }

    public function createOrUpdateSamplingQuestion(Request $request, $questionId = 0)
    {
        $user = Auth::user();
        if ($questionId == 0) {
            $question = new SamplingQuestion();
        } else {
            $question = SamplingQuestion::findOrFail($questionId);
            if (!$question->hasAccess($user)) {
                $this->message = "Current user doesn't have edit access to the requested question.";
                Log::debug($this->message);
                return $this->getKnowledges($request);
            }
        }
        $question->question = $request->input('question');
        $question->depth =  $request->input('depth');
        $question->save();

        $create = $request->input();
        $knowledges = [];
        foreach ($create as $label => $input) {
            if (strpos($label, 'knowledge_') !== false) {
                $knowledges[] = $input;
            }
        }
        $question->editors()->attach($user, ['write_access' => true]);
        $question->knowledges()->detach();
        foreach ($knowledges as $knowledge) {
            $know = Knowledge::firstOrNew(['name' => strtolower($knowledge)]);
            $question->knowledges()->attach($know);
        }
        $question->save();
        $title = "Sampling Question";
        return $this->adminView('curriculum/question/edit', [
            'question' => $question,
            'knowledges' => $question->getCurrentKnowledges(),
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
