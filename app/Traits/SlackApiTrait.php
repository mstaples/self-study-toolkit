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

trait SlackApiTrait
{
    public $defaultView;

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
                        ]
                    ]
                ]
            ]
        ];
    }

    protected function firstSampling()
    {
        return $this->defaultView;
    }

    protected function nextPrompt($user)
    {
        return $this->defaultView;
    }

    public function parseActions(Operator $operator, $slackActions)
    {
        foreach ($slackActions as $action) {
            $slackAction = new SlackAction($action);
            $slackAction->parseAction($operator);
        }
        return $this->nextPrompt($operator);
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

    public function createSamplingBlock()
    {
        $block_id = $prompt->path->category->id . '.' .
            $prompt->path->id . '.' .
            $prompt->id .  '.' .
            $segment;

    }
}
