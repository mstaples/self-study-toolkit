<?php

use Illuminate\Database\Seeder;

class PathCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // habits > communication > interaction > process > initiative
        $defaultCategories = [
            0 => [
                'name' => 'Habits',
                'per' => 'Week',
                'min' => '2',
                'max' => '7',
                'span' => '3',
                'description' => 'Habits are small ways of thinking or acting which we can repeat intentionally to shift our unintentional behaviors overtime.'
            ],
            1 => [
                'name' => 'Communications',
                'per' => 'Month',
                'min' => '4',
                'max' => '12',
                'span' => '2',
                'description' => 'Communications include what, how, and to whom we say, write, hear, and read.'
            ],
            2 => [
                'name' => 'Interactions',
                'per' => 'Quarter',
                'min' => '6',
                'max' => '36',
                'span' => '1',
                'description' => 'Interactions are growth practices which require the participation of at least one other human.'
            ],
            3 => [
                'name' => 'Processes',
                'per' => 'Quarter',
                'min' => '3',
                'max' => '24',
                'span' => '2',
                'description' => 'Processes are rules around how people go about accomplishing tasks which are similar or which need to be performed repeatedly overtime.'
            ],
            4 => [
                'name' => 'Initiatives',
                'per' => 'Year',
                'min' => '12',
                'max' => '48',
                'span' => '1',
                'description' => 'Initiatives combine interactions and processes to create systemic changes overtime.'
            ]
        ];
        foreach ($defaultCategories as $category) {
            DB::table('path_categories')->insert([
                'name' => $category['name'],
                'per' => $category['per'],
                'min' => $category['min'],
                'max' => $category['max'],
                'span' => $category['span'],
                'state' => 'live',
                'description' => $category['description'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
