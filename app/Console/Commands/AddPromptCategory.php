<?php

namespace App\Console\Commands;

use App\Objects\PathCategory;
use App\Traits\PathTrait;
use Illuminate\Console\Command;

class AddPromptCategory extends Command
{
    use PathTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:prompt-category';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a category for prompt paths.';

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
        $categories = PathCategory::all();
        if ($categories) {
            $list = '';
            foreach ($categories as $category) {
                $list .= " * " . $category->name;
            }
            $this->info("Current categories: ".$list);
        }

        $create = new PathCategory();
        $maxPer = $this->getPerMax();
        $name = $this->ask("New category name? ");
        $description = $this->ask("What's a good description for this category? ");
        $per = $this->choice("What time scale makes sense for this category of paths? ", ['Week', 'Month', 'Quarter', 'Year'], 1);

        $min = $max = $span = 0;
        while ($min < 1 || $min > $maxPer[$per]['min']) {
            $min = $this->ask("What's the minimum number of check-ins per $per for a path in this category? ");
        }
        while ($max < 1 || $max > $maxPer[$per]['max']) {
            $max = $this->ask("What's the maximum number of check-ins per $per for a path in this category? ");
        }
        $span = $this->choice("How many ".$per."s will paths in this category run? ", [ 1 => 1, 2 => 2 ], 2);

        $create->name = $name;
        $create->description = $description;
        $create->per = $per;
        $create->min = $min;
        $create->max = $max;
        $create->span = $span;
        $create->save();

        $this->info("New prompt category -- $name -- created!");
        return;
    }
}
