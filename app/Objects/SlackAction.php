<?php

namespace App\Objects;

use App\Objects\FeedbackRecord;
use App\Objects\Operator;
use App\Objects\Prompt;
use App\Objects\PromptSegmentResponse;
use App\Objects\SamplingAnswer;
use App\Objects\SamplingQuestion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class SlackAction extends Model
{
    /*
     * block ids are constructed by App/Objects/Answer::getBlockId()
     * block ids match the pattern: question_type .  question_id . answer_id
     * [prompt_segment] . prompt_segment_id . prompt_segment_response_id
     * [feedback_request] . feedback_request_id . feedback_response_id
     * [sampling_question] . sampling_question_id . sampling_answer_id
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
        Log::debug($slackAction);
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

    public function getBlockType()
    {
        return $this->type;
    }

    public function getContentType()
    {
        $components = explode('.',$this->block_id);
        return $components[0];
    }

    public function getContentId()
    {
        $components = explode('.',$this->block_id);
        return $components[1];
    }

    public function getAnswerId()
    {
        Log::debug($this->block_id);
        $components = explode('.', $this->block_id);
        return $components[2];
    }

    public function isSaveAction()
    {
        $components = explode('.', $this->block_id);
        if (array_key_exists(3, $components) && $components[3] == 'save') {
            return true;
        }
        return false;
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
        $answers = [ 'sampling_question', 'prompt_segment', 'feedback_request' ];
        $contentType = $this->getContentType();
        Log::debug(__METHOD__.': '.$contentType);
        if (in_array($contentType, $answers)) {
            $answerId = $this->getAnswerId();
            if ($answerId == 'next') {
                $travel = $operator->getCurrentTravel();
                if (empty($travel)) {
                    Log::debug("takeAction answerId next but no current travel found. review path");
                    return 'review';
                }
                $travel->completed_segments += 1;
                $travel->save();
                return 'segment';
            }
            $answer = $operator->retrieveAnswer($contentType, $answerId);
            Log::debug($answer);
            if ($this->isSaveAction()) {
                Log::debug(__METHOD__.": return saveAnswer()");
                return $answer->saveAnswer($operator);
            }
            Log::debug(__METHOD__.": return answerQuestion()");
            return $answer->answerQuestion($this->getBlockType(), $answer, $this->value);
        }
        switch($contentType) {
            case 'actions':
                $actionType = $this->getContentId();
                Log::debug("$actionType: $this->value");
                if ($actionType == 'goto') return $this->value;
                if ($actionType == 'review') return $actionType;
                break;
            case 'demo':
                return $this->block_id;
            case 'preferences':
                $preferenceAction = $this->getContentId();
                switch($preferenceAction) {
                    case 'user':
                        Log::debug("Slack user attempting to connect curriculum editor account. ");
                        $connecting_user_id = $this->getAnswerId();
                        $connecting_code = $this->value;
                        Log::debug("operator->connectUser($connecting_user_id, $connecting_code)");
                        $operator->connectUser($connecting_user_id, $connecting_code);
                        return 'preferences';
                    case 'topics':
                    case 'frequency':
                        if (!is_array($this->value)) {
                            $preference = $operator->preferences()->where('type', $preferenceAction)->first();
                            if (!empty($preference)) {
                                $preference->name = $this->value;
                                $preference->save();
                                return 'preferences';
                            }
                            $preference = new Preference([
                                'type' => $preferenceAction,
                                'name' => $this->value
                            ]);
                            $operator->preferences()->save($preference);
                            $preference->save();
                            return 'preferences';
                        }
                        $preferences = $operator->preferences()->where('type', $preferenceAction)->get();
                        foreach ($preferences as $preference) {
                            if (!in_array($preference->name, $this->value)) {
                                $preference->delete();
                                continue;
                            }
                            $key = array_search($preference->name, $this->value);
                            unset($this->value[$key]);
                        }
                        if (!empty($this->value)) {
                            foreach ($this->value as $new) {
                                $preference = new Preference();
                                $preference->type = $preferenceAction;
                                $preference->name = $new;
                                $operator->preferences()->save($preference);
                                $preference->save();
                            }
                        }
                        return 'preferences';
                    case 'done':
                        return $operator->needsAQuestion();
                    default:
                        if ($this->value = 'save') {
                            Log::debug(__METHOD__.": return needsAQuestion()");
                            return $operator->needsAQuestion();
                        }
                }
                break;
            case 'done':
                return $operator->needsAQuestion();
            case 'path':
            $id = $this->value[0];
            $path = PromptPath::find($id);
            if (empty($path)) {
                Log::debug("Path action supplied id \"$id\" but no such path exists.");
                return 'wait';
            }
            //'operator_id', 'path_id', 'completed', 'completed_at', 'level', 'notebook'
            $travel = new Travel();
            $travel->level = $path->path_level;
            $operator->travels()->save($travel);
            $path->travels()->save($travel);
            $travel->save();

            return 'prompt';
        }
        return 'wait';
    }
}
