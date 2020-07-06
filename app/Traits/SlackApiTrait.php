<?php

namespace App\Traits;

use App\Objects\Operator;
use App\Objects\Prompt;
use App\Objects\PromptPath;
use App\Objects\PromptSegment;
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

    protected function nextSegment(Operator $operator)
    {
        Log::debug(__METHOD__);
        $segment = $operator->getNextSegment();
        Log::debug(__METHOD__);
        Log::debug($segment);
        if (is_object($segment)) {
            return $this->createSegmentView($operator, $segment);
        }
        switch($segment) {
            case 'rest':
                return $this->createRestView($operator);
            case 'path':
                return $this->nextPath($operator);
            case 'question':
                $question = $operator->pickSamplingQuestion();
                $user_id = $operator->slack_user_id;
                $answer = $question->prepareNewQuestionAnswer($operator);
                return $this->createSamplingQuestionView($answer, $user_id);
            case 'prompt':
                /*
                $current = $operator->getCurrentTravel();
                if (!empty($current) && !empty($current->current_travel)) {
                    $segment = $operator->getNextSegment();
                    return $this->createSegmentView($operator, $segment);
                }
                $prompt = $operator->getNextPrompt();
                $current->
                $current->*/
                    //return $this->createSegmentView($operator, $segment);
                break;
            default:
                break;
        }
    }

    protected function nextPath(Operator $operator)
    {
        Log::debug(__METHOD__);
        $preferences = $operator->preferences()->where('type', 'topics')->get();
        if (empty($preferences)) {
            Log::debug("No preferences found");
            return $this->topicPreferences($operator);
        }
        if ($operator->needsAQuestion() == 'question') {
            $question = $operator->pickSamplingQuestion();
            $user_id = $operator->slack_user_id;
            $answer = $question->prepareNewQuestionAnswer($operator);
            return $this->createSamplingQuestionView($answer, $user_id);
        }
        $path = $operator->travels()->where('completed', false)->first();
        if (!empty($path)) {
            return $this->nextSegment($operator);
        }
        $pathOptions = $operator->pathOptions();
        $user_id = $operator->slack_user_id;
        $title = "Learning paths";
        $description = "Select one of the available paths for your next learning journey or adjust your preferences.";
        return $this->createPathMenuView($user_id, $title, $description, $pathOptions);
    }

    protected function topicPreferences(Operator $operator, $block = false)
    {
        Log::debug(__METHOD__);
        $topics = $operator->getTopicPreferences();
        $user_id = $operator->slack_user_id;
        $block_id = "preferences.topics";
        $title = "Focus Area";
        $description = "Select topics you're interested in focusing on to help surface relevant questions and paths.";
        $label = "Available options:";
        if ($block) {//$block_id, $label, $options, $selected = []
            $selected = $operator->getPreferredTopics();
            return $this->createCheckboxesBlock($block_id, $label, $topics, $selected);
        }
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

    public function createSegmentView(Operator $operator, PromptSegment $segment)
    {
        Log::debug(__METHOD__);
        $user_id = $operator->slack_user_id;

        $this->setDefaultHomeTab();
        $view = $this->defaultView;
        $view['user_id'] = $user_id;
        $view['view']['blocks'] = [];
        $view['view']['blocks'][] = $this->createPromptBlock($segment->prompt);

        $title = $segment->segment_title;
        $description = $segment->segment_text;
        $type = $segment->accessory_type;
        $image = $segment->segment_imageUrl;
        $url = $segment->segment_url;
        $block_id = 'prompt_segment.next.next';

        switch($type) {
            case 'button':
            case 'info':
                $view['view']['blocks'][] = $this->createDescriptiveButtonBlock($block_id, $title, $description, 'next', 'next', $url);
                return $view;
                break;
            case 'image':
                $view['view']['blocks'][] = $this->createImageBlock($block_id, $title, $description, $image);
                return $view;
                break;
            case 'checkboxes':
                $answer = $segment->prepareNewQuestionAnswer($operator);
                $block_id = $answer->getBlockId();
                $label = "Select any answers you think make sense:";
                $message = '*' . $title . "* \n _" . $description . '_';
                $view['view']['blocks'][] = $this->createMessageBlock($message);
                $view['view']['blocks'][] = $this->createCheckboxesBlock($block_id, $label, $answer->available_options);
                $view['view']['blocks'][] = $this->createButtonBlock($block_id.'.save', 'Done with selection', 'save', 'save');
                return $view;
                break;
            case 'radio_buttons':
                $answer = $segment->prepareNewQuestionAnswer($operator);
                $block_id = $answer->getBlockId();
                $label = " ";
                $message = '*' . $title . "* \n _" . $description . '_';
                $view['view']['blocks'][] = $this->createMessageBlock($message);
                $view['view']['blocks'][] = $this->createRadioButtonsBlock($block_id, $label, $answer->available_options);
                return $view;
            default:
                $message = "Segment \"$title\" provided unknown type: $type";
                $segment->accessory_type = 'info';
                $segment->save();
                Log::debug($message);
                return $this->createSegmentView($operator, $segment);
                break;
        }
    }

    public function createMessageView($message)
    {
        $view = $this->messageView;
        $view['view']['blocks'][0]['text']['text'] = $message;
        return $view;
    }

    public function createTitledMessageView($title, $message)
    {
        Log::debug(__METHOD__);
        $markdown = "*$title* \n $message";
        $view = $this->messageView;
        $view['view']['blocks'][0]['text']['text'] = $markdown;
        return $view;
    }

    public function createRestView(Operator $operator)
    {
        $view = $this->defaultHome();

        $view['user_id'] = $operator->slack_user_id;
        $view['view']['blocks'] = [];
        $message = "*Process and reflect* \n _You've completed as many prompts as align with your goals for the moment._";
        $view['view']['blocks'][] = $this->createMessageBlock($message);
        $block_id = "preferences.review";
        $message = "You can change the frequency at which you receive new prompts, or adjust other preferences.";
        $view['view']['blocks'][] = $this->createButtonBlock($block_id, $message, "Preferences", 'review');

        $travel = $operator->getCurrentTravel();
        if (empty($travel)) {
            return $this->nextPath($operator);
        }
        $last = $travel->responses()->orderBy('updated_at', 'desc')->first();
        if (empty($last)) {
            return $this->nextSegment($operator);
        }
        $segment = PromptSegment::find($last->question_id);
        $prompt = $segment->prompt;
        $path = $prompt->prompt_path;
        $title = $prompt->prompt_title;
        $block_id = "path.replay";
        $message = "You can replay your last prompt, $title, if you would like to review it.";
        $view['view']['blocks'][] = $this->createButtonBlock($block_id, $message, "Replay", $path->id);

        return $view;
    }

    public function createPreferencesView(Operator $operator)
    {
        $view = $this->defaultHome();

        $view['user_id'] = $operator->slack_user_id;
        $view['view']['blocks'] = [];
        $message = "*Preferences* \n ";
        $view['view']['blocks'][] = $this->createMessageBlock($message);
        $view['view']['blocks'][] = $this->topicPreferences($operator, true);

        $block_id = "preferences.frequency";
        $frequencies = array_keys($operator->frequencies);
        $current = $operator->preferences()->where('type', 'frequency')->first();
        $selected = null;
        if (!empty($current)) $selected = $current->name;
        $options = [];
        foreach ($frequencies as $option) {
            $options[$option] = [ 'option' => $option ];
            $options[$option]['has'] = $selected == $option;
        }
        $label = "New prompt frequency: ";
        $view['view']['blocks'][] = $this->createRadioButtonsBlock($block_id, $label, $options);

        $block_id = "preferences.done";
        $view['view']['blocks'][] = $this->createButtonBlock($block_id.'.save', 'Done with changes', 'save', 'save');

        return $view;
    }

    public function createPathMenuView($user_id, $title, $description, $options)
    {
        $view = $this->defaultHome();
        $message = "*$title* \n $description";

        $view['user_id'] = $user_id;
        $view['view']['blocks'] = [];
        $view['view']['blocks'][] = $this->createMessageBlock($message);
        $view['view']['blocks'][] = $this->createMessageBlock(" --- ");

        $label = 'Select';
        foreach ($options as $id => $info) {
            $block_id = 'path.select.' . $id;
            $block_title = $info['name'];
            $block_description = $info['description'];
            $block = $this->createDescriptiveButtonBlock($block_id, $block_title, $block_description, $label, $id);
            $view['view']['blocks'][] = $block;
        }

        $view['view']['blocks'][] = $this->createMessageBlock(" --- ");

        $block_id = "preferences.review";
        $title = "Review preferences";
        $description = "You can change the frequency at which you receive new prompts, or adjust other preferences.";
        $message = "*$title* \n _ $description _";
        $view['view']['blocks'][] = $this->createButtonBlock($block_id, $message, "Preferences", 'review');

        return $view;
    }

    public function createCheckboxesView($user_id, $block_id, $title, $description, $label, $options)
    {
        $view = $this->defaultHome();
        $message = "*$title* \n $description";

        $view['user_id'] = $user_id;
        $view['view']['blocks'] = [];
        $view['view']['blocks'][] = $this->createMessageBlock($message);
        $view['view']['blocks'][] = $this->createCheckboxesBlock($block_id, $label, $options);
        $view['view']['blocks'][] = $this->createButtonBlock($block_id.'.save', 'Done with selection', 'save', 'save');

        return $view;
    }

    public function formatSlackOptions($availableOptions)
    {
        $options = [];
        $selected = [];
        foreach ($availableOptions as $id => $option) {
            $options[$id] = $option['option'];
            if (array_key_exists('has', $option) && $option['has']) {
                $selected[$id] = $option['option'];
            }
        }
        return [ 'options' => $options, 'initial_options' => $selected ];
    }

    public function createSamplingQuestionView($answer, $user_id)
    {
        $title = "Current understandings ping";
        $description = "_Please, answer a few questions to help surface useful learning paths wherever you currently are on your journey._";

        $block_id = $answer->getBlockId();
        $label = $answer->question_text;

        return $this->createCheckboxesView($user_id, $block_id, $title, $description, $label, $answer->available_options);
    }

    public function createPromptBlock(Prompt $prompt)
    {
        $path = $prompt->prompt_path;
        $title = "*".$path->path_title. ": ".$prompt->prompt_title."* \n ";
        $thesis = "_".$path->path_thesis."_ \n";
        return $this->createMessageBlock($title.$thesis);
    }

    public function createRadioButtonsBlock($block_id, $label, $options)
    {
        Log::debug(__METHOD__);
        $slackOptions = $this->formatSlackOptions($options);
        $options = $slackOptions['options'];
        $selected = $slackOptions['initial_options'];
        $accessoryOptions = [];
        foreach ($options as $value => $option) {
            $accessoryOptions[] = [
                'value' => "$value",
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
        if (!empty($selected)) {
            $value = array_key_first($selected);
            $text = array_shift($selected);
            $block['accessory']['initial_option'] = [
                'value' => "$value",
                'text' => [
                    "type" => "plain_text",
                    "text" => $text
                ]
            ];
        }
        return $block;
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

    public function createCheckboxesBlock($block_id, $label, $options, $selected = [])
    {
        Log::debug(__METHOD__);
        $slackOptions = $this->formatSlackOptions($options);
        $options = $slackOptions['options'];
        $selected = $slackOptions['initial_options'];
        $accessoryOptions = $initialOptions = [];
        foreach ($options as $id => $option) {
            $item = [
                'value' => "$id",
                'text' => [
                    "type" => "plain_text",
                    "text" => $option
                ]
            ];
            $accessoryOptions[] = $item;
            if (!empty($selected) && in_array($option, $selected)) {
                $initialOptions[] = $item;
            }
        }
        $block = [
            'type' => 'section',
            'block_id' => $block_id,
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

    public function createDescriptiveButtonBlock($block_id, $title, $description, $label, $value, $url = null)
    {
        Log::debug(__METHOD__);
        if ($url != null) {
            $description .= " ($url)";
        }
        $text = "*$title* \n _$description _";
        $block = [
            'type' => 'section',
            'block_id' => $block_id,
            'text' => [
                'type' => 'mrkdwn',
                'text' => $text
            ],
            'accessory' => [
                "type" => "button",
                "value" => "$value",
                'text' => [
                    'type' => 'plain_text',
                    'text' => $label,
                    'emoji' => true
                ]
            ]
        ];
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
                "value" => "$value",
                'text' => [
                    'type' => 'plain_text',
                    'text' => $name,
                    'emoji' => true
                ]
            ]
        ];
        return $block;
    }

    public function createDescriptiveChoiceBlock($block_id, $label, $options)
    {
        Log::debug(__METHOD__);
        $accessoryOptions = [];
        foreach ($options as $id => $info) {
            $description = $info['description'];
            if (strlen($description) > 149) {
                $description = substr($description, 0, 145);
                $description .= '...';
            }
            $accessoryOptions[] = [
                'value' => "$id",
                'text' => [
                    "type" => "mrkdwn",
                    "text" => '*' . $info['name'] . '*'
                ],
                'description' => [
                    "type" => "mrkdwn",
                    "text" => '_' . $description . '_'
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

    public function createSamplingBlock(SamplingAnswer $answer)
    {
        Log::debug("createSamplingBlock: ".$answer->question_text);
        // sampling question: 'state', 'depth', 'question', 'answer_options'
        // sampling options: 'sampling_question_id', 'question_text', 'option', 'correct', 'state'
        $options = $answer->getOptions();
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
            'block_id' => $answer->getBlockId(),
            'text' => [
                'type' => 'mrkdwn',
                'text' => $answer->question_text
            ],
            'accessory' => [
                "type" => "radio_buttons",
                "options" => $accessoryOptions
            ]
        ];
        return $block;
    }

    public function initiatePathDemo($json)
    {
        $path_id = $json['path_id'];
        $user_id = $json['user_id'];
        $slack_user_id = $json['slack_user_id'];
        $user = User::find($user_id);
        $operator = $user->operator;
        $path = PromptPath::findOrFail($path_id);

        $pathOptions = $operator->pathOptions();
        Log::debug($pathOptions);
        if (!array_key_exists($path_id, $pathOptions)) {
            $pathOptions[$path->id] = [
                'name' => $path->path_title,
                'description' => $path->path_thesis
            ];
        }
        $title = "Learning paths";
        $description = "Select one of the available paths for your next learning journey or adjust your preferences.";
        return $this->createPathMenuView($slack_user_id, $title, $description, $pathOptions);
    }

    public function initiatePromptDemo($json)
    {
        $prompt_id = $json['prompt_id'];
        $user_id = $json['user_id'];
        $slack_user_id = $json['slack_user_id'];
        $user = User::find($user_id);
        $operator = $user->operator;
        $prompt = Prompt::findOrFail($prompt_id);
        $path = $prompt->prompt_path;

        $segment = $prompt->prompt_segments()->where('prompt_segment_order', 1)->first();

        $view = $this->createSegmentView($operator, $segment);
        $view['view']['blocks'][] = [ 'type' => 'divider' ];
        $message = "Viewing a prompt outside the context of a path may have strange results for your account";
        $view['view']['blocks'][] = $this->createMessageBlock($message);

        return $view;
    }

    public function constructConnectUserJson($json)
    {
        $code = $json['connect_code'];
        $user_id = $json['user_id'];
        $slack_user_id = $json['slack_user_id'];

        $json = [
            'user_id' => $slack_user_id,
            'view' => [
                'type' => 'home',
                'blocks' => []
            ]];

        $message = "*Connect to curriculum editor account* \n _Select the code generated by your user account to connect it to this Slack account to run curriculum demos._ \n";
        $json['view']['blocks'][] = $this->createMessageBlock($message);

        $block_id = "preferences.user.".$user_id;
        $label = "Options:";
        $options = [];
        $new = md5($user_id);
        for ($i = 0; $i < 5; $i++) {
            $new = str_shuffle($new);
            $options[] = substr($new,8,2).'-'.substr($new,5,3).'-'.substr($new,2,2);
        }
        $options[] = $code;
        shuffle($options);
        $formattedOptions = [];
        foreach ($options as $option) {
            $formattedOptions[$option] = [ 'option' => $option ];
        }
        $json['view']['blocks'][] = $this->createRadioButtonsBlock($block_id, $label, $formattedOptions);

        return $json;
    }

    public function parseAction(Operator $operator, $action)
    {
        $slackAction = new SlackAction();
        $slackAction->buildAction($action);
        $next = $slackAction->takeAction($operator);
        Log::debug(__METHOD__.": $next");
        // getContentType = sampling_question or preferences or feedback or prompt
        switch ($next) {
            case 'question':
                $question = $operator->pickSamplingQuestion();
                $user_id = $operator->slack_user_id;
                $answer = $question->prepareNewQuestionAnswer($operator);
                return $this->createSamplingQuestionView($answer, $user_id);
            case 'segment':
            case 'prompt':
                $prompt = $operator->getNextPrompt();
                if ($prompt == 'path') {
                    return $this->nextPath($operator);
                }
                $travel = $operator->getCurrentTravel();
                $travel->current_prompt()->dissociate();
                $travel->current_prompt()->associate($prompt);
                $travel->save();

                return $this->nextSegment($operator);
            case 'path':
                return $this->nextPath($operator);
            case 'rest':
                return $this->createRestView($operator);
            case 'preferences':
                return $this->createPreferencesView($operator);
            case 'wait':
                return [];
        }
    }
}
