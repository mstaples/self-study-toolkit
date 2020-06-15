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
        $this->type = $slackAction['type'];
        $this->block_id = $slackAction['block_id'];
        switch($this->type) {
            case 'radio_buttons':
                $this->value = $slackAction['selected_option']['value'];
            break;
            default;
                $this->valud = $slackAction['value'];
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

    public function getCount()
    {
        $components = explode('.',$this->block_id);
        return $components[2];
    }

    public function getAssociatedId($type=null)
    {
        if ($this->block_id == getenv('SLACK_SAVE_USER_BLOCK_ID')) {
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
        switch($this->getType()) {
            case 'sampling_question':
                $samplingQuestionId = $this->getId();
                $question = SamplingQuestion::find($samplingQuestionId);
                $option = SamplingOption::find($this->value);

                $action = new SamplingAnswer();
                $action->operator()->associate($operator);
                $action->sampling_question_id = $samplingQuestionId;
                $action->sampling_question = $question->question;
                $action->answer = $option->option;
                $action->correct = $option->correct;
                $action->save();

                return $this->getCount() + 1;
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
        }
        return;
    }
}
