<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([PathCategoriesTableSeeder::class]);
        $this->call([PromptPathsTableSeeder::class]);
        $this->call([SamplingQuestionsTableSeeder::class]);
    }
}
