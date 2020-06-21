<?php

use App\Objects\Knowledge;
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
        // 'state', 'depth', 'question', 'answer_options'
        // depths: 'vague', 'passing', 'familiar', 'deep'
        // options: 'sampling_question_id', 'question_text', 'option', 'correct', 'state'
        // seed path titles: "Circle of trust", "Hometown bias", "Stupendous Badass"
        $defaultQuestions = [
            0 => [
                "knowledges" => [ "relationships", "teamwork", "introspection", "community"],
                'state' => 'live',
                'depth' => 'passing',
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
                "knowledges" => [ "relationships", "teamwork", "introspection", "community"],
                'state' => 'live',
                'depth' => 'familiar',
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
                "knowledges" => [ "relationships", "teamwork", "introspection", "community"],
                'state' => 'live',
                'depth' => 'vague',
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
                "knowledges" => [ "relationships", "teamwork", "introspection", "community"],
                'state' => 'live',
                'depth' => 'deep',
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
            $id = DB::table('sampling_questions')->insertGetId([
                'state' => $question['state'],
                'question' => $question['question'],
                'depth' => $question['depth'],
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
            foreach ($question['knowledges'] as $knowledge) {
                $know = DB::table('knowledges')
                    ->select('id')
                    ->where('name', $knowledge)
                    ->first();
                if (empty($know)) {
                    $knowId = DB::table('knowledges')->insertGetId([
                        'name' => $knowledge,
                        'description' => '',
                        'prerequisites' => false
                    ]);
                } else {
                    $knowId = $know->id;
                }

                DB::table('knowledges_questions')->insert([
                    'question_id' => $id,
                    'knowledge_id' => $knowId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}
