<?php

namespace App\Objects;

use App\Objects\FeedbackRecord;
use App\Objects\Operator;
use App\Objects\Prompt;
use App\Objects\PromptResponse;
use App\Objects\SamplingAnswer;
use App\Objects\SamplingQuestion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class SlackAction extends Model
{
    /*
     * block ids are constructed:
     * [prompt] . prompt_category_id . prompt_path_id . prompt_id . segment
     * [feedback] . feedback_request_id
     * [sampling_question] . sampling_question_id . count
     * */
    public $block_id;
    public $type;
    public $value;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function buildAction($slackAction)
    {
        //Log::debug($slackAction);
        $this->type = $slackAction['type'];
        if (array_key_exists('block_id', $slackAction)) {
            $this->block_id = $slackAction['block_id'];
        } else {
            $this->block_id = $slackAction['action_id'];
        }
        switch($this->type) {
            case 'radio_buttons':
                $this->value = $slackAction['selected_option']['value'];
            break;
            case 'checkboxes':
                $selected = $slackAction['selected_options'];
                Log::debug($selected);
                $this->value = [];
                foreach ($selected as $selection) {
                    $this->value[] = $selection['value'];
                }
            break;
            default;
                $this->value = $slackAction['value'];
            break;
        }
        return;
    }

    public function getType()
    {
        $components = explode('.',$this->block_id);
        return $components[0];
    }

    public function getId()
    {
        $components = explode('.',$this->block_id);
        return $components[1];
    }

    public function getAssociatedId($type=null)
    {
        if ($this->block_id == config('services.slack.save_id')) {
            return null;
        }
        $components = explode('.',$this->block_id);
        if ($type == null) {
            return $components[1];
        }
        $types = ['type', 'category', 'path', 'prompt', 'segment'];
        if (!in_array($type, $types)) return null;

        return $components[array_search($type,$types)];
    }

    public function takeAction(Operator $operator)
    {
        Log::debug(__METHOD__.': '.$this->getType());
        switch($this->getType()) {
            case 'sampling_question':
                $samplingQuestionId = $this->getId();
                $question = SamplingQuestion::find($samplingQuestionId);
                $option = SamplingOption::find($this->value);

                $action = new SamplingAnswer();
                $action->sampling_question_id = $samplingQuestionId;
                $action->question_text = $question->question;
                $action->answer = $option->option;
                $action->correct = $option->correct;
                $action->depth = $question->depth;
                $action->operator()->associate($operator);
                $action->save();

                return $operator->needsAQuestion();
                break;
            case 'prompt':
                $promptId = $this->getAssociatedId('prompt');
                $prompt = Prompt::find($promptId);

                $action = new PromptResponse();
                $action->prompt_id = $promptId;
                $action->prompt_title = $prompt->prompt_title;
                $action->response = $this->value;
                $action->save();
                break;
            case 'feedback':
                $feedbackRequestId = $this->getAssociatedId();
                $request = SamplingQuestion::find($feedbackRequestId);

                $action = new FeedbackRecord();
                $action->feedback_request_id = $feedbackRequestId;
                $action->feedback_request = $request->request;
                $action->record = $this->value;
                $action->save();
                break;
            case 'preferences':
                if ($this->value == 'save') {
                    return false;
                }
                Log::debug(__METHOD__.': set preferences : '.$this->getId());
                Log::debug($this->value);
                $operator->preferences[$this->getId()] = $this->value;
                $operator->save();

                return false;
                break;
        }
        return false;
    }
}
