<?php

use Illuminate\Database\Seeder;

class SamplingQuestionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 'state', 'question_difficulty', 'question', 'answer_options'
        // difficulties: 'vague', 'passing', 'familiar', 'deep'
        // options: 'sampling_question_id', 'question_text', 'option', 'correct', 'state'
        // seed path titles: "Circle of trust", "Hometown bias", "Stupendous Badass"
        $defaultQuestions = [
            0 => [
                'path_title' => 'Circle of trust',
                'state' => 'live',
                'question_difficulty' => 'passing',
                'question' => "What's the biggest factor in building trust?",
                'answer_options' => [
                    [
                    'option' => 'Repeated interactions',
                    'correct' => true,
                    'state' => 'live'
                    ],
                    [
                    'option' => "Common experiences",
                    'correct' => false,
                    'state' => 'live'
                    ]
                ]
            ],
            1 => [
                'path_title' => 'Circle of trust',
                'state' => 'live',
                'question_difficulty' => 'familiar',
                'question' => "What behavior most benefits untrustworthy actors?",
                'answer_options' => [[
                    'option' => 'Always giving the benefit of the doubt, regardless of previous actions.',
                    'correct' => true,
                    'state' => 'live'
                ],
                [
                    'option' => 'Also being an untrustworthy actor.',
                    'correct' => false,
                    'state' => 'live'
                ]]
            ],
            2 => [
                'path_title' => 'Circle of trust',
                'state' => 'live',
                'question_difficulty' => 'vague',
                'question' => "When do untrustworthy actors have an advantage?",
                'answer_options' => [[
                    'option' => 'On an initial interaction with no known history.',
                    'correct' => true,
                    'state' => 'live'
                ],
                [
                    'option' => 'Over long term relationships',
                    'correct' => false,
                    'state' => 'live'
                ]]
            ],
            3 => [
                'path_title' => 'Circle of trust',
                'state' => 'live',
                'question_difficulty' => 'deep',
                'question' => 'Which two behaviors together build trustworthy community?',
                'answer_options' => [[
                    'option' => 'Investing in long term relationships and giving benefit of the doubt -- or not -- based on others behavior.',
                    'correct' => true,
                    'state' => 'live'
                ],
                [
                    'option' => 'Always choosing to trust and constantly seek new relationships.',
                    'correct' => false,
                    'state' => 'live'
                ]]
            ]
        ];
        foreach ($defaultQuestions as $question) {
            $path_id = DB::table('prompt_paths')->where('path_title', $question['path_title'])->first()->id;
            $id = DB::table('sampling_questions')->insertGetId([
                'prompt_path_id' => $path_id,
                'state' => $question['state'],
                'question' => $question['question'],
                'question_difficulty' => $question['question_difficulty'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
            foreach ($question['answer_options'] as $option) {
                DB::table('sampling_options')->insert([
                    'state' => $question['state'],
                    'sampling_question_id' => $id,
                    'question_text' => $question['question'],
                    'option' => $option['option'],
                    'correct' => $option['correct'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}
