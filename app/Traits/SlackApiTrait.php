<?php

namespace App\Traits;

use App\Objects\FeedbackRecord;
use App\Objects\Operator;
use App\Objects\Prompt;
use App\Objects\PromptResponse;
use App\Objects\SamplingAnswer;
use App\Objects\SamplingQuestion;
use App\Objects\SlackAction;
use App\Objects\User;
use Illuminate\Support\Facades\Log;

trait SlackApiTrait
{
    public $defaultView;
    public $messageView = [
            'view' => [
                'type' => 'home',
                'blocks' => [
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => ''
                        ]]]]];

    public function setDefaultHomeTab()
    {
        $this->defaultView = [
            'view' => [
                'type' => 'home',
                'blocks' => [
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => 'Welcome to the self study toolkit!'
                        ]
                    ],
                    [
                        'type' => 'section',
                        'block_id' => getenv('SLACK_SAVE_USER_BLOCK_ID'),
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => 'This app would like to use your Slack profile'.
                                ' information to create and save progress on your journey.'.
                                ' When you\'re ready to get started, use the button to indicate your agreement.'
                        ],
                        'accessory' => [
                            'type' => 'button',
                            'text' => [
                                'type' => 'plain_text',
                                'text' => 'Remember Me'
                            ],
                            'style' => 'primary',
                            'value' => 'new-journey'
                        ]]]]];
    }

    protected function firstSampling($user_id)
    {
        $questions = SamplingQuestion::where('state', 'live')
            ->whereIn('question_difficulty', [ 'vague' ])
            ->first();
        return $this->createSamplingQuestionView($questions, $user_id, 1);
    }

    protected function nextPrompt($user)
    {
        return $this->defaultView;
    }

    public function parseAction(Operator $operator, $action)
    {
        $slackAction = new SlackAction();
        $slackAction->buildAction($action);
        $count = $slackAction->takeAction($operator);

        switch($slackAction->type) {
            case 'sampling_question':
                if ($count < 5) {
                    $question = $operator->pickSamplingQuestion();
                    $user_id = $operator->slack_user_id;
                    return $this->createSamplingQuestionView($question, $user_id, $count);
                }
                return $this->nextPrompt($operator);
            break;
            case 'feedback':
            case 'prompt':
            default:
                return $this->nextPrompt($operator);
            break;
        }
    }

    public function createMessageView($message)
    {
        $view = $this->messageView;
        $view['view']['blocks'][0]['text']['text'] = $message;
        return $view;
    }

    public function createSamplingQuestionView($question, $user_id, $count)
    {
        $description = "Please, answer a few questions to help surface useful learning paths wherever you currently are on your journey. ($count / 5)";
        $this->setDefaultHomeTab();
        $view = $this->defaultView;
        $view['user_id'] = $user_id;
        $view['view']['blocks'] = [];
        $view['view']['blocks'][] = $this->createMessageBlock($description);
        $view['view']['blocks'][] = $this->createSamplingBlock($question, $count);
        return $view;
    }

    public function createMessageBlock($message)
    {
        return [
            'type' => 'section',
            'text' => [
                'type' => 'mrkdwn',
                'text' => $message
            ]
        ];
    }

    public function createPromptBlock(Prompt $prompt, $segment)
    {
        $block_id = $prompt->path->category->id . '.' .
            $prompt->path->id . '.' .
            $prompt->id .  '.' .
            $segment;
    }

    public function createFeedbackBlock()
    {
        $block_id = $prompt->path->category->id . '.' .
            $prompt->path->id . '.' .
            $prompt->id .  '.' .
            $segment;

    }

    public function createSamplingBlock(SamplingQuestion $question, $count)
    {
        Log::debug("createSamplingBlock: ".$question->question);
        // sampling question: 'state', 'question_difficulty', 'question', 'answer_options'
        // sampling options: 'sampling_question_id', 'question_text', 'option', 'correct', 'state'
        $options = $question->sampling_options;
        $accessoryOptions = [];
        foreach ($options as $option) {
            $accessoryOptions[] = [
                'value' => "$option->id",
                'text' => [
                    "type" => "plain_text",
                    "text" => $option->option
                ]
            ];
        }
        $block = [
            'type' => 'section',
            'block_id' => 'sampling_question.'.$question->id.'.'.$count,
            'text' => [
                'type' => 'mrkdwn',
                'text' => $question->question
            ],
            'accessory' => [
                "type" => "radio_buttons",
                "options" => $accessoryOptions
            ]
        ];
        return $block;
    }
}
