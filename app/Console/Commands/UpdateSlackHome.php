<?php

namespace App\Console\Commands;

use App\Traits\SlackApiTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateSlackHome extends Command
{
    use SlackApiTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:slack-home {json?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Slack app home view.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    private $client;

    public function __construct(Client $guzzleClient)
    {
        parent::__construct();
        $this->client = $guzzleClient;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setDefaultHomeTab();
        $json = $this->argument('json') === null ? $this->defaultView : $this->argument('json');
        $json['user_id'] = getenv('USER_ID');
        Log::debug('UpdateSlackHome json keys? '. implode(', ', array_keys($json)));
        Log::debug($json);
        $headers =  [
            'Authorization' => 'Bearer ' . getenv('BOT_TOKEN'),
            'Content-type'      => 'application/json; charset=utf-8'
        ];

        $setup = [
            'headers' => $headers,
            'json' => $json
        ];

        $url = getenv('SLACK_URL').'/views.publish';

        $this->info('Updating Slack app Home: '.$url);
        try {
            $response = $this->client->request('POST', $url, $setup)->getBody()->getContents();
            $response = json_decode($response, true);
            Log::debug($response);
            if (!array_key_exists('response_metadata',$response)
                || !array_key_exists('messages',$response['response_metadata'])) {
                var_dump($response);
                return;
            }
            foreach ($response['response_metadata']['messages'] as $message) {
                $this->info($message);
            }
//            $this->info($response['result']['status']);

        } catch (ClientException $e) {
            $response = $e->getResponse();
            $this->error($response->getBody()->getContents());

            return;
        }
    }
}
