<?php

namespace App\Console\Commands;

use App\Objects\PathCategory;
use App\Objects\Knowledge;
use App\Objects\SamplingAnswer;
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
    protected $signature = 'add:question';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a sampling question.';

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
        $depths = $this->getDepths();
        $text = $this->ask('What is the new sampling question?');
        $depth = $this->choice('What depth of understanding does this sampling question reflect?', $depths);
        $knowledges = $this->ask('Comma separated list of knowledges you associate with this question?');
        $knowledges = explode(',', $knowledges);
        $question = new SamplingQuestion();
        $question->question = $text;
        $question->depth = $depth;
        $question->save();

        foreach ($knowledges as $knowledge) {
            $topic = DB::table('knowledges')->where('name', $knowledge)->first();
            if (empty($topic)) {
                Log::debug("Unknown knowledge requested in Commands/AddQuestion ($knowledge)");
                continue;
            }
            $question->knowledges()->attach($knowledge);
        }
        $add = true;
        $choices = [ 'yes' => 'Yes', 'no' => 'No' ];
        $this->info("Add answer options:");
        while($add) {
            $option = $this->ask('Answer text: ');
            $correct = $this->choice('Is this a correct answer option? ', $choices, 'no');
            $correct = $correct == 'no' ? false : true;

            $new = new SamplingAnswer();
            $new->answer = $option;
            $new->correct = $correct;
            $new->depth = $question->depth;
            $new->sampling_question = $question->question;
            $new->save();

            $question->question_options()->associate($new);

            $again = $this->choice('Add another option? ', $choices, 'yes');
            $add = $again == 'yes' ? true : false;
        }
    }
}
