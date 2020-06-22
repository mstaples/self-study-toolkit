<?php

namespace App\Http\Controllers;

use App\Objects\Operator;
use App\Traits\SlackApiTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SlackController extends Controller
{
    use SlackApiTrait;

    public function actions(Request $request)
    {
        $body = $request->toArray();
        $body = json_decode($body['payload'], true);
        $user = $body['user'];
        $operator = $this->retrieveOperator($user);
        $type = $body['type'];
        switch($type) {
            case 'block_actions':
                $actions = $body['actions'];
                if (!$operator->opt_in && $actions[0]['block_id'] != config('services.slack.save_id')) {
                    Log::debug('SlackController:actions no opt in agreement found, return default');
                    $this->setDefaultHomeTab();
                    $json = $this->defaultView;
                } elseif ($actions[0]['block_id'] == config('services.slack.save_id')) {
                    Log::debug('SlackController:actions new opt in agreement made, send first sampling');
                    $operator->opt_in = true;
                    $operator->save();
                    $json = $this->firstSampling($operator->slack_user_id);
                } else {
                    Log::debug('SlackController:actions operator found, parse actions');
                    foreach ($actions as $action) {
                        Log::debug($action);
                        $json = $this->parseAction($operator, $action);
                    }
                }
                break;
            case 'shortcut':
            case 'message_actions':
            case 'view_submission':
            case 'view_closed':
            default:
                $message = "Unexpected interaction type requested: $type";
                $json = $this->parseAction($operator, $this->createMessageView($message));
                break;
        }

        Log::debug("update:slack-home");
        //Log::debug($json);
        $exitCode = Artisan::call('update:slack-home', [
            'json' => $json
        ]);

        Log::debug("SlackController:actions update:slack-home exit code: $exitCode");

        return response()->json([]);
    }
}
