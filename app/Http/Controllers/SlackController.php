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
        $actions = $body['actions'];
        $operator = Operator::where('slack_user_id', $user['id'])->first();
        if (empty($operator) && $actions[0]['block_id'] != getenv('SLACK_SAVE_USER_BLOCK_ID')) {
            Log::debug('SlackController:actions no operator, no agreement, return default');
            $json = $this->defaultView;
        } elseif (empty($operator) && $actions[0]['block_id'] == getenv('SLACK_SAVE_USER_BLOCK_ID')) {
            Log::debug('SlackController:actions no operator, found agreement, make new operator, send first sampling');
            $operator = new Operator();
            $operator->slack_user_id = $user['id'];
            $operator->name = $user['name'];
            $operator->opt_in = true;
            $operator->save();
            $json = $this->firstSampling();
        } elseif ($actions[0]['block_id'] == getenv('SLACK_SAVE_USER_BLOCK_ID')) {
            Log::debug('SlackController:actions operator found, record agreement, send first sampling');
            $operator->opt_in = true;
            $operator->save();
            $json = $this->firstSampling();
        } else {
            Log::debug('SlackController:actions operator found, parse actions');
            $json = $this->parseActions($operator, $actions);
        }

        $exitCode = Artisan::call('update:slack-home', [
            'json' => $json
        ]);

        Log::debug("SlackController:actions update:slack-home exit code: $exitCode");

        return response()->json([]);
    }
}
