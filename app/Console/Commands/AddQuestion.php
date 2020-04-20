<?php

namespace App\Console\Commands;

use App\Objects\PathCategory;
use App\Objects\PromptPath;
use App\Objects\SamplingQuestion;
use App\Traits\PathTrait;
use Illuminate\Console\Command;

class AddQuestion extends Command
{
    use PathTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:question {path_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a sampling question for a prompt path.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $path_id = $this->argument('path_id');
        $difficulties = $this->getDifficulties();
        if (!$path_id || !$path = PromptPath::find($path_id)) {
            $categories = PathCategory::where('state', 'live')->pluck('name')->toArray();
            $category = $this->choice('To which category does this sampling question relate?', $categories);
            $difficulty = $this->choice('What experience level does this sampling question gauge?', $difficulties);

            $paths = PromptPath::where([
                'path_category' => $category,
                'path_difficulty' => $difficulty
            ])->get();
            if (empty($paths->items)) {
                $this->info("There are no $difficulty $category paths yet.");
                return;
            }
            var_dump($paths);
            $options = [];
            foreach($paths as $path) {
                $options[$path->id] = $path->path_title;
            }
            $path = $this->choice('Which prompt path does this sampling question reflect?', $options);
            $path_id = array_search($path, $options);
        }
        $title = $path->path_title;
        $this->info('Path title: ' . $title);
        $this->info('Path thesis: ' . $path->path_thesis);
        $question_text = $this->ask("What is the new sampling question for this path?");
        $answer_options = [];
        for ($i = 0;$i < 3; $i++) {
            $answer_options[$i] = [ 'text' => '', 'indicator' => true ];
            $answer_options[$i]['text'] = $this->ask("Please, provide an answer which would indicate understanding of the thesis of this path ($i/3) ");
        }
        $this->info("Thank you");
        for ($i = 3;$i < 8; $i++) {
            $answer_options[$i] = [ 'text' => '', 'indicator' => false ];
            $count = $i - 3;
            $answer_options[$i]['text'] = $this->ask("Please, provide an answer which would indicate unfamiliarity with the thesis of this path ($count/5) ");
        }

        $question = new SamplingQuestion();
        $question->path_id = $path_id;
        $question->question = $question_text;
        $question->answer_options = $answer_options;
        $question->save();
    }
}
