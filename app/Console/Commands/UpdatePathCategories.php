<?php

namespace App\Console\Commands;

use App\Objects\PathCategory;
use App\Traits\PathTrait;
use Illuminate\Console\Command;

class UpdatePathCategories extends Command
{
    use PathTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:path-categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'View and make changes to prompt category details.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function updatePathCategory(PromptCategory $category)
    {
        $maxPer = $this->getPerMax();
        $name = $this->ask("Update category name? ", $category->name);
        $description = $this->ask("Update description for this category? ", $category->description);
        $per = $this->choice("Update time scale for this category of paths? ", ['Week', 'Month', 'Quarter', 'Year'], $category->per);

        $min = $max = $span = 0;
        while ($min < 1 || $min > $maxPer[$per]['min']) {
            $min = $this->ask("Update the minimum number of check-ins per $per for a path in this category? ", $category->min);
        }
        while ($max < 1 || $max > $maxPer[$per]['max']) {
            $max = $this->ask("Update the maximum number of check-ins per $per for a path in this category? ", $category->max);
        }
        $span = $this->choice("Update the number of ".$per."s paths in this category run? ", [ 1 => 1, 2 => 2 ], $category->span);

        $category->name = $name;
        $category->description = $description;
        $category->per = $per;
        $category->min = $min;
        $category->max = $max;
        $category->span = $span;
        $category->save();

        $this->info("New prompt category -- $name -- updated!");

        return true;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $categories = PathCategory::all();
        $categoryNames = [ 0 => 'none (exit)' ];
        $categoryLibrary = [];
        if ($categories) {
            foreach ($categories as $category) {
                $categoryNames[] = $category->name;
                $categoryLibrary[$category->name] = $category;
            }
        }
        while (true) {
            $name = $this->choice("Select a category: ", $categoryNames, 0);
            if ($name == $categoryNames[0]) { break; }
            $category = $categoryLibrary[$name];
            $this->info($name);
            $this->info($category->description);
            $this->info($category->min . ' - ' . $category->max . ' steps over ' . $category->span . ' ' . $category->per);
            $edit = $this->choice("Make changes to this category? ", ['yes', 'no'], 'yes');
            if ($edit == 'yes') {
                $this->updatePathCategory($category);
            }
        }
        return;
    }
}
