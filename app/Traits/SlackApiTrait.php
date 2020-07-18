<?php

namespace App\Traits;

use App\Objects\Operator;
use App\Objects\Prompt;
use App\Objects\PromptPath;
use App\Objects\PromptSegment;
use App\Objects\PromptSegmentResponse;
use App\Objects\SamplingAnswer;
use App\Objects\SamplingQuestion;
use App\Objects\SlackAction;
use App\Objects\Travel;
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
        $user = User::find($user_id);
        $operator = $user->operator;
        return $this->promptDemoStep($operator, $prompt_id, 1);
    }

    public function initiateReplay(Operator $operator)
    {
        $last = $operator->travels()->where('completed', true)->orderByDesc('completed_at')->first();
        if (empty($last)) return false;
        $path = $last->prompt_path;

        $travel = new Travel();
        $travel->level = $path->path_level;
        $operator->travels()->save($travel);
        $path->travels()->save($travel);
        $travel->save();

        return true;
    }

    public function promptDemoStep(Operator $operator, $promptId, $step)
    {
        $this->setDefaultHomeTab();
        $view = $this->defaultView;
        $view['view']['blocks'] = [];
        $message = "Viewing a prompt outside the context of a path may have strange results for your account";
        $view['view']['blocks'][] = $this->createMessageBlock($message);
        $view['view']['blocks'][] = [ 'type' => 'divider' ];

        $prompt = Prompt::findOrFail($promptId);
        $segment = $prompt->prompt_segments()->where('prompt_segment_order', $step)->first();
        if (!$segment) {
            $message = "You have walked through all of the currently available segments for this prompt.";
            $view['view']['blocks'][] = $this->createMessageBlock($message);
            $view['view']['blocks'][] = [ 'type' => 'divider' ];
            $view['view']['blocks'][] = $this->createRefreshHomeBlock();
            return $view;
        }
        $view = $this->createSegmentView($operator, $segment, true);

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
                if ($prompt == 'rest') {
                    return $this->createRestView($operator);
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
            case 'replay':
                $possible = $this->initiateReplay($operator);
                if ($possible) return $this->nextSegment($operator);
                return $this->nextSegment($operator);
            case 'pause':
                return $this->createPauseView($operator);
                break;
            case 'back':
                $travel = $operator->getCurrentTravel();
                if (empty($travel)) return $this->createRestView();
                $segment = $travel->getLastSegment();
                if (empty($segment)) {
                    $path = $travel->prompt_path;
                    $prompt = $path->prompts()->where('prompt_path_step', 1)->first();
                    $step = $travel->completed_segments > 0 ? $travel->completed_segments : 1;
                    $segment = $prompt->prompt_segments()->where('prompt_segment_order', $step)->first();
                }
                return $this->createSegmentView($operator, $segment);
            case 'wait':
                return [];
            default:
                if (strpos($next, 'demo') !== false) {
                    $unpack = explode('.', $next);
                    $promptId = $unpack[1];
                    $lastStep = $unpack[2];
                    return $this->promptDemoStep($operator, $promptId, ++$lastStep);
                }
                return [];
        }
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
            return $this->createPreferencesView($operator);
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
        if (empty($pathOptions)) {
            Log::debug("No available paths found");
            return $this->createPreferencesView($operator);
        }
        $user_id = $operator->slack_user_id;
        $title = "Learning paths";
        $description = "Select one of the available paths for your next learning journey or adjust your preferences.";
        return $this->createPathMenuView($user_id, $title, $description, $pathOptions);
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

    public function formatSlackOptions($availableOptions, $shuffle = true)
    {
        $options = [];
        $selected = [];
        foreach ($availableOptions as $id => $option) {
            $options[$id] = $option['option'];
            if (array_key_exists('has', $option) && $option['has']) {
                $selected[$id] = $option['option'];
            }
        }
        if ($shuffle) {
            $ids = array_keys($options);
            shuffle($ids);
            $random = [];
            foreach ($ids as $id) {
                $random[$id] = $options[$id];
            }
            $options = $random;
        }
        return [ 'options' => $options, 'initial_options' => $selected ];
    }

    public function createSegmentView(Operator $operator, PromptSegment $segment, $demo = false)
    {
        Log::debug(__METHOD__);
        $user_id = $operator->slack_user_id;

        $this->setDefaultHomeTab();
        $view = $this->defaultView;
        $view['user_id'] = $user_id;
        $view['view']['blocks'] = [];
        $view['view']['blocks'][] = $this->createPromptBlock($segment->prompt);
        $view['view']['blocks'][] = [ 'type' => 'divider' ];

        $title = $segment->segment_title;
        $description = $segment->segment_text;
        $type = $segment->accessory_type;
        $image = $segment->segment_image_url;
        $alt_text = $segment->image_alt_text;
        $url = $segment->segment_url;
        $block_id = 'prompt_segment.next.next';
        $prompt = $segment->prompt;
        if ($demo) {
            $block_id = 'demo.' . $prompt->id . '.' . $segment->prompt_segment_order;
        }
        if (strlen($url) > 3) {
            $description = $description .' (' . $url . ')';
        }
        Log::debug("createSegmentView block id = " . $block_id . " and demo == " . $demo);

        switch($type) {
            case 'button':
            case 'info':
                $view['view']['blocks'][] = $this->createDescriptiveButtonBlock($block_id, $title, $description, 'next', 'next');
                break;
            case 'image':
                $message = '*' . $title . "* \n " . $description;
                $view['view']['blocks'][] = $this->createImageBlock($block_id, $image, $alt_text, $message);
                $block_id = $block_id . '.next';
                $view['view']['blocks'][] = $this->createButtonBlock($block_id, ' ', 'next', 'next');
                break;
            case 'checkboxes':
                $answer = $segment->prepareNewQuestionAnswer($operator);
                $block_id = $answer->getBlockId();
                $label = "Select any answers you think make sense:";
                $message = '*' . $title . "* \n " . $description;
                $previous = $operator->getLastAnswer($segment);
                $selected_options = empty($previous) ? [] : $previous->selected_options;
                $view['view']['blocks'][] = $this->createMessageBlock($message);
                $view['view']['blocks'][] = $this->createCheckboxesBlock($block_id, $label, $answer->available_options, $selected_options);
                $view['view']['blocks'][] = $this->createButtonBlock($block_id.'.save', 'Done with selection', 'save', 'save');
                break;
            case 'radio_buttons':
                $answer = $segment->prepareNewQuestionAnswer($operator);
                $block_id = $answer->getBlockId();
                $label = " ";
                $message = '*' . $title . "* \n " . $description;
                $view['view']['blocks'][] = $this->createMessageBlock($message);
                $view['view']['blocks'][] = $this->createRadioButtonsBlock($block_id, $label, $answer->getSegmentOptions());
                $last = $operator->getLastAnswer($segment);
                if ($last) {
                    $view['view']['blocks'][] = $this->createButtonBlock($block_id.'.save', 'Done with selection', 'save', 'save');
                }
                break;
            default:
                $message = "Segment \"$title\" provided unknown type: $type";
                $segment->accessory_type = 'info';
                $segment->save();
                Log::debug($message);
                $view = $this->createSegmentView($operator, $segment);
                break;
        }
        $view['view']['blocks'][] = [ "type" => "divider" ];

        $travel = $operator->getCurrentTravel();
        if (empty($travel) || empty($travel->getLastSegment())) {
            $view['view']['blocks'][] = $this->createFooterBlock([ "preferences", "pause" ]);
        } else {
            $view['view']['blocks'][] = $this->createFooterBlock([ "back", "preferences", "pause" ]);
        }

        return $view;
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

        $travel = $operator->getCurrentTravel();
        if (empty($travel)) {
            return $this->nextPath($operator);
        }
        if (($travel->completed_prompts == 0 && $travel->completed_segments == 0) ||
            $travel->readyForNextPrompt()) {
            return $this->nextSegment($operator);
        }

        $view['view']['blocks'] = [];
        $message = "*Process and reflect* \n _You've completed as many prompts as align with your goals for the moment._";
        $view['view']['blocks'][] = $this->createMessageBlock($message);
        $view['view']['blocks'][] = $this->createFooterBlock(["preferences", "replay"]);

        return $view;
    }

    public function createPauseView(Operator $operator)
    {
        $view = $this->defaultHome();

        $view['user_id'] = $operator->slack_user_id;

        $travel = $operator->getCurrentTravel();
        if (empty($travel)) {
            return $this->nextPath($operator);
        }

        $view['view']['blocks'] = [];
        $message = "*Paused* \n _Restart your journey at any time._";
        $view['view']['blocks'][] = $this->createMessageBlock($message);
        $view['view']['blocks'][] = [ 'type' => 'divider' ];
        $view['view']['blocks'][] = $this->createFooterBlock(["preferences", "back"]);

        return $view;
    }

    public function createPreferencesView(Operator $operator)
    {
        $view = $this->defaultHome();

        $view['user_id'] = $operator->slack_user_id;
        $view['view']['blocks'] = [];
        $message = "*Preferences* \n ";
        $view['view']['blocks'][] = $this->createMessageBlock($message);
        $view['view']['blocks'][] = [ 'type' => 'divider' ];
        $view['view']['blocks'][] = $this->createTopicPreferencesBlock($operator);
        $view['view']['blocks'][] = $this->createPromptFrequencyPreferenceBlock($operator);

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
        $view['view']['blocks'][] = [ "type" => "divider" ];

        $label = 'Select';
        foreach ($options as $id => $info) {
            $block_id = 'path.select.' . $id;
            $block_title = $info['name'];
            $block_description = $info['description'];
            $block = $this->createDescriptiveButtonBlock($block_id, $block_title, $block_description, $label, $id);
            $view['view']['blocks'][] = $block;
        }

        $view['view']['blocks'][] = [ "type" => "divider" ];
        $view['view']['blocks'][] = $this->createFooterBlock([ "preferences" ]);

        return $view;
    }

    public function createCheckboxesView($user_id, $block_id, $title, $description, $label, $options)
    {
        $view = $this->defaultHome();
        $message = "*$title* \n $description";

        $view['user_id'] = $user_id;
        $view['view']['blocks'] = [];
        $view['view']['blocks'][] = $this->createMessageBlock($message);
        $view['view']['blocks'][] = [ 'type' => 'divider' ];
        $view['view']['blocks'][] = $this->createCheckboxesBlock($block_id, $label, $options);
        $view['view']['blocks'][] = [ 'type' => 'divider' ];
        $view['view']['blocks'][] = $this->createButtonBlock($block_id.'.save', 'Done with selection', 'save', 'save');

        return $view;
    }

    public function createSamplingQuestionView($answer, $user_id)
    {
        $title = "Current understandings ping";
        $description = "_Please, answer a few questions to help surface useful learning paths wherever you currently are on your journey._";

        $block_id = $answer->getBlockId();
        $label = $answer->question_text;

        $view = $this->createCheckboxesView($user_id, $block_id, $title, $description, $label, $answer->available_options);
        $view['view']['blocks'][] = $this->createFooterBlock([ "preferences", "pause" ]);

        return $view;
    }

    public function createFooterBlock($options = [])
    {
        Log::debug(__METHOD__);
        $block = [
            'type' => 'actions',
            'block_id' => 'actions.footer.action',
            'elements' => []
            ];
        $elements = [
            "replay" => [ "value" => "goto.replay", "text" => "Replay" ],
            "back" => [ "value" => "goto.back", "text" => "Back" ],
            "preferences" => [ "value" => "goto.preferences", "text" => "Preferences" ],
            "pause" => [ "value" => "goto.pause", "text" => "Pause" ],
        ];
        if (empty($options)) {
            $options = [ "preferences" ];
        }
        foreach ($options as $option) {
            $block['elements'][] = [
                "type" => "button",
                "value" => $elements[$option]['value'],
                'text' => [
                    'type' => 'plain_text',
                    'text' => $elements[$option]['text'],
                    'emoji' => true
                ]];
        }
        return $block;
    }

    public function createRefreshHomeBlock()
    {
        Log::debug(__METHOD__);
        $block = [
            'type' => 'section',
            'block_id' => 'done.refresh.home',
            'text' => [
                'type' => 'mrkdwn',
                'text' => "*Return Home* _Click the *Done* button when you're ready to refresh your toolkit homepage._"
            ],
            'accessory' => [
                "type" => "button",
                "value" => "home.refresh",
                'text' => [
                    'type' => 'plain_text',
                    'text' => "Done",
                    'emoji' => true
                ]
            ]
        ];
        return $block;
    }

    public function createPromptFrequencyPreferenceBlock(Operator $operator)
    {
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
        return $this->createRadioButtonsBlock($block_id, $label, $options, false);
    }

    public function createTopicPreferencesBlock(Operator $operator)
    {
        Log::debug(__METHOD__);
        $topics = $operator->getTopicPreferences();
        $block_id = "preferences.topics";
        $label = "Available options:";
        $selected = $operator->getPreferredTopics();
        return $this->createCheckboxesBlock($block_id, $label, $topics, $selected, false);
    }

    public function createPromptBlock(Prompt $prompt)
    {
        $path = $prompt->prompt_path;
        $title = "*".$path->path_title. ": ".$prompt->prompt_title."* \n ";
        $thesis = "_".$path->path_thesis."_ \n";
        return $this->createMessageBlock($title.$thesis);
    }

    public function createRadioButtonsBlock($block_id, $label, $options, $shuffle = true)
    {
        Log::debug(__METHOD__);
        $slackOptions = $this->formatSlackOptions($options, $shuffle);
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

    public function createImageBlock($blockId, $imageUrl, $altText, $message)
    {
        return [
            "type" => "section",
            "block_id" => $blockId,
			"text" => [
			    "type" => "mrkdwn",
				"text" => $message
			],
			"accessory" => [
            "type" => "image",
				"image_url" => $imageUrl,
				"alt_text" => $altText
			]
        ];
    }

    public function createCheckboxesBlock($block_id, $label, $options, $shuffle = true)
    {
        Log::debug(__METHOD__);
        $slackOptions = $this->formatSlackOptions($options, $shuffle);
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
}
