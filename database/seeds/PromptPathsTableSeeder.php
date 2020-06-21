<?php

use App\Objects\Prompt;
use App\Objects\PathCategory;
use App\Objects\PromptPath;
use App\Objects\PromptSegment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class PromptPathsTableSeeder extends Seeder
{
    public function getPromptSegments($segmentsArray, Prompt $prompt)
    {
        foreach ($segmentsArray as $key => $segment) {
            $new = new PromptSegment();
            $new->segment_title = $segment['title'];
            $new->segment_text = $segment['text'];
            $new->segment_type = $segment['type'];
            $new->segment_imageUrl = $segment['imageUrl'];
            $new->segment_url = $segment['url'];
            $new->prompt_segment_order = $key;
            $new->prompt_id = $prompt->id;
            if (!array_key_exists('options', $segment['accessory'])) {
                var_dump("seed accessory record missing options key: ".$segment['title']);
            }
            $new->segment_accessory = $new->createAccessory(
                $segment['accessory']['type'],
                $segment['accessory']['options'],
                $segment['accessory']['text'],
                $segment['accessory']['value']);
            $new->save();
            $prompt->prompt_segments()->save($new);
        }
        return true;
    }
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Log::debug(__DIR__."/paths.json");
        $importJson = file_get_contents(__DIR__."/paths.json");
        $defaultPaths = json_decode($importJson, true);
        // habits > communication > interaction > process > initiative
        foreach ($defaultPaths as $path) {
            if ($path['ready'] != true) continue;
            $category = PathCategory::where('name', $path['path_category'])->first();
            $newPath = PromptPath::create([
                'state' => 'live',
                'steps' => count($path['prompts']),
                'path_title' => $path['path_title'],
                'path_level' => $path['path_level'],
                'path_thesis' => $path['path_thesis'],
                'path_category' => $path['path_category'],
                'path_category_id' => $category->id
            ]);

            foreach ($path['knowledges'] as $knowledge) {
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

                DB::table('knowledges_paths')->insert([
                    'path_id' => $newPath->id,
                    'knowledge_id' => $knowId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            foreach ($path['prompts'] as $step => $prompt) {
                $create = Prompt::create([
                    'prompt_path_step' => $step,
                    'prompt_path_id' => $newPath->id,
                    'prompt_title' => $prompt['prompt_title'],
                    'repeatable' => $prompt['repeatable'],
                    'optional' => $prompt['optional']
                ]);
                $this->getPromptSegments($prompt['prompt_segments'], $create);
            }
        }
    }
}
