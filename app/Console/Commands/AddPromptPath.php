<?php

namespace App\Console\Commands;

use App\Objects\FormElement;
use App\Objects\Prompt;
use App\Objects\PathCategory;
use App\Objects\PromptPath;
use App\Objects\PromptSegment;
use App\Traits\PathTrait;
use Illuminate\Console\Command;

class AddPromptPath extends Command
{
    use PathTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:prompt-path';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add an ordered series of steps that aim to facilitate a specific growth experience.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function addFormElements()
    {
        $expandForm = 'yes';
        $formElements = [];
        while ($expandForm == 'yes') {
            $expandForm = $this->choice("Add a form element to this segment? ", ['yes', 'no'], 'yes');
            if ($expandForm != 'yes') { break; }
            $formElement = new FormElement();
            $type = $this->choice("What type of form element? ", $formElement->types);
            $formElement->setFormElementType($type);
            $attributes = $formElement->getAttributes();
            foreach ($attributes as $attribute => $value) {
                $attributes[$attribute] = $this->ask("$type $attribute? ");
            }
            $formElements[] = $attributes;
        }

        return $formElements;
    }

    protected function addSegments(Prompt $prompt)
    {
        $segments = [];
        $addAnother = 'yes';
        while ($addAnother == 'yes') {
            $addAnother = $this->choice("Add a segment to this prompt? ", ['yes', 'no'], 'yes');
            if ($addAnother != 'yes') { break; }

            $segment = new PromptSegment();
            $segment->title = $this->ask("What's a good title for this segment? ");
            $segment->type = $this->choice("What kind of segment is this? ", $prompt->getSegmentTypes());
            $segment->formElements = $this->addFormElements();

            $segments[] = $segment;
        }
        return $segments;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function handle()
    {
        $categories = PathCategory::all();
        $categoryNames = [];
        if ($categories) {
            foreach ($categories as $category) {
                $categoryNames[] = $category->name;
            }
        }
        $categoryName = $this->choice("Select a category: ", $categoryNames);
        $category = PathCategory::where('name', $categoryName)->first();
        $this->info($categoryName);
        $this->info($category->description);
        $this->info($category->min . ' - ' . $category->max . ' steps over ' . $category->span . ' ' . $category->per);
        $pathName = $this->ask("What's a good title for the new $categoryName path? ");
        $difficulty = $this->choice("How difficult do you intend the new $categoryName path to be? ",
            $this->getDifficulties());
        $steps = $category->max * $category->span;
        $minSteps = $category->min * $category->span;
        $tags = $this->ask("Comma separated list of tags you'd like added to this path: ");
        $tags = explode(',', $tags);

        $path = new PromptPath ();
        $path->path_title = $pathName;
        $path->path_difficulty = $difficulty;
        $path->path_category = $categoryName;
        $path->steps = $steps;
        $path->tags = json_encode($tags);
        $path->category()->associate($category);
        $path->save();

        $continue = $this->choice("$categoryName need $steps prompts to be created. Operators may choose to see as few as " .
            $minSteps .
            ". They will always be presented in the order you provide. The path will always start" .
            " with your first prompt and end with your last prompt regardless of the total number of prompts" .
            " the operator has chosen for their journey. A prompt can be any mix of media and interactive" .
            " components, any length, and can link out to additional resources.", ["let's do it", "later"], 0);
        if ($continue == "let's do it") {
            $prompts = 0;
            while ($prompts < $steps) {
                $this->info("Creating prompt ".++$prompts." of $steps");
                $prompt = new Prompt();
                $prompt->promptTitle = $this->ask("What's a good title for this prompt? ");
                $this->info("Segments can provide more info, link to external resources, or ask for an interaction
                from the operator.");
                $segments = $this->addSegments($prompt);
                $prompt->promptSegments = $segments;
                $prompt->prompt_path()->save($path);
                $prompt->save();
            }
        }
        $this->info("Successfully created a new $categoryName path!");
    }
}
