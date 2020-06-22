<?php

namespace App\Traits;

use App\Objects\Operator;
use App\Objects\Prompt;
use App\Objects\PromptPath;
use App\Objects\SamplingQuestion;
use App\Objects\SlackAction;
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
                        'block_id' => config('services.slack.save_id'),
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
                        ]
                    ]
                ]
            ]
        ];
    }

    public function defaultHome()
    {
        $this->setDefaultHomeTab();
        Log::debug(__METHOD__ . ': ' . env('SLACK_SAVE_USER_BLOCK_ID'));
        return $this->defaultView;
    }

    protected function firstSampling($user_id)
    {
        $questions = SamplingQuestion::where('state', 'live')
            ->whereIn('depth', [ 'vague' ])
            ->first();
        return $this->createSamplingQuestionView($questions, $user_id);
    }

    protected function nextPrompt(Operator $operator)
    {
        Log::debug(__METHOD__);
        $path = $operator->travels()->where('completed', false)->first();
        if (empty($path)) {
            Log::debug("No path found");
            return $this->nextPath($operator);
        }
        return $this->defaultHome();
    }

    protected function nextPath(Operator $operator)
    {
        Log::debug(__METHOD__);
        $preferences = $operator->preferences;
        if (empty($preferences) ||
            !array_key_exists('topics', $preferences) ||
            empty($preferences['topics'])) {
            Log::debug("No preferences found");
            return $this->topicPreferences($operator);
        }
        $path = $operator->travels()->where('completed', false)->first();
        if (!empty($path)) {
            return $this->nextPrompt($operator);
        }
        $pathOptions = $operator->pathOptions();
        $user_id = $operator->slack_user_id;
        $label = 'Learning path options';
        $title = "Learning paths";
        $description = "Select one of the available paths for your next learning journey.";
        return $this->createSelectView($user_id, 'path.select', $title, $description, $label, $pathOptions);
    }

    protected function topicPreferences(Operator $operator)
    {
        Log::debug(__METHOD__);
        $topics = $operator->getTopicPreferences();
        $user_id = $operator->slack_user_id;
        $block_id = "preferences.topics";
        $title = "Focus Area";
        $description = "Select topics you're interested in focusing on to help surface relevant questions and paths.";
        $label = "Available options:";
        return $this->createCheckboxesView($user_id, $block_id, $title, $description, $label, $topics);
    }

    public function retrieveOperator($user)
    {
        $operator = Operator::where('slack_user_id', $user['id'])->first();
        if (empty($operator)) {
            $operator = new Operator();
            $operator->slack_user_id = $user['id'];
            $operator->name = $user['name'];
            $operator->nickname = $user['username'];
            $operator->save();
        }
        //Log::debug($operator);
        return $operator;
    }

    public function createMessageView($message)
    {
        $view = $this->messageView;
        $view['view']['blocks'][0]['text']['text'] = $message;
        return $view;
    }

    public function createSelectView($user_id, $block_id, $title, $description, $label, $options)
    {
        $view = $this->defaultHome();
        $message = "*$title*\n $description";

        $view['user_id'] = $user_id;
        $view['view']['blocks'] = [];
        $view['view']['blocks'][] = $this->createMessageBlock($message);
        $view['view']['blocks'][] = $this->createMultipleChoiceBlock($block_id, $label, $options);

        return $view;
    }

    public function createCheckboxesView($user_id, $block_id, $title, $description, $label, $options)
    {
        $view = $this->defaultHome();
        $message = "*$title*\n $description";
        $buttons = [ 'save' => 'save' ];

        $view['user_id'] = $user_id;
        $view['view']['blocks'] = [];
        $view['view']['blocks'][] = $this->createMessageBlock($message);
        $view['view']['blocks'][] = $this->createCheckboxesBlock($block_id, $label, $options);
        $view['view']['blocks'][] = $this->createButtonBlock($block_id.'.save', 'Done with selection', 'save', 'save');

        return $view;
    }

    public function createSamplingQuestionView($question, $user_id)
    {
        $description = "Please, answer a few questions to help surface useful learning paths wherever you currently are on your journey.";
        $this->setDefaultHomeTab();
        $view = $this->defaultView;
        $view['user_id'] = $user_id;
        $view['view']['blocks'] = [];
        $view['view']['blocks'][] = $this->createMessageBlock($description);
        $view['view']['blocks'][] = $this->createSamplingBlock($question);
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

    public function createCheckboxesBlock($action_id, $label, $options)
    {
        Log::debug(__METHOD__);
        $accessoryOptions = [];
        $selectedOptions = [];
        foreach ($options as $option => $checked) {
            $item = [
                'value' => "$option",
                'text' => [
                    "type" => "plain_text",
                    "text" => $option
                ]
            ];
            $accessoryOptions[] = $item;
            if ($checked) {
                $selectedOptions[] = $item;
            }
        }
        $block = [
            'type' => 'section',
            'block_id' => $action_id,
            'text' => [
                'type' => 'mrkdwn',
                'text' => $label
            ],
            'accessory' => [
                "type" => "checkboxes",
                "options" => $accessoryOptions
            ]
        ];
        if (!empty($initialOptions)) {
            $block['accessory']['initial_options'] = $initialOptions;
        }
        return $block;
    }

    public function createButtonBlock($block_id, $label, $name, $value)
    {
        Log::debug(__METHOD__);
        $block = [
            'type' => 'section',
            'block_id' => $block_id,
            'text' => [
                'type' => 'mrkdwn',
                'text' => $label
            ],
            'accessory' => [
                "type" => "button",
                "value" => $value,
                'text' => [
                    'type' => 'plain_text',
                    'text' => $name,
                    'emoji' => true
                ]
            ]
        ];
        return $block;
    }

    public function createMultipleChoiceBlock($block_id, $label, $options)
    {
        Log::debug(__METHOD__);
        $accessoryOptions = [];
        foreach ($options as $option) {
            $accessoryOptions[] = [
                'value' => "$option",
                'text' => [
                    "type" => "plain_text",
                    "text" => $option
                ]
            ];
        }
        $block = [
            'type' => 'section',
            'block_id' => $block_id,
            'text' => [
                'type' => 'mrkdwn',
                'text' => $label
            ],
            'accessory' => [
                "type" => "radio_buttons",
                "options" => $accessoryOptions
            ]
        ];
        return $block;
    }

    public function createSamplingBlock(SamplingQuestion $question)
    {
        Log::debug("createSamplingBlock: ".$question->question);
        // sampling question: 'state', 'depth', 'question', 'answer_options'
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
            'block_id' => 'sampling_question.'.$question->id,
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

    public function parseAction(Operator $operator, $action)
    {
        $slackAction = new SlackAction();
        $slackAction->buildAction($action);
        $repeat = $slackAction->takeAction($operator);
        $textRepeat = $repeat ? 'repeat' : 'no-repeat';

        switch($slackAction->getType()) {
            case 'sampling_question':
                Log::debug(__METHOD__.": Ask another question? ".$textRepeat);
                if ($repeat) {
                    $question = $operator->pickSamplingQuestion();
                    Log::debug($question);
                    $user_id = $operator->slack_user_id;
                    return $this->createSamplingQuestionView($question, $user_id);
                }
                return $this->nextPrompt($operator);
                break;
            case 'preferences':
            case 'feedback':
            case 'prompt':
            default:
                return $this->nextPrompt($operator);
                break;
        }
    }
}
