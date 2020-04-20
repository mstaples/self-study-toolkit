<?php

namespace App\Console\Commands;

use App\Objects\PathCategory;
use App\Objects\PromptPath;
use App\Traits\GoogleSheetsApiTrait;
use Illuminate\Console\Command;

class ImportPrompts extends Command
{
    use GoogleSheetsApiTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:prompts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import prompts from a google spreadsheet';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function assemblePrompt(array $row)
    {
        $segments = [
            1 => [
                'title' => $row[2],
                'type' => $row[3],
                'info_text' => $row[5],
                'info_links' => explode(',', $row[6]),
                'image_url' => $row[7],
                'image_text' => $row[8],
                'action_type' => explode(',', $row[9]),
                'action_desc' => $row[10]
            ],
            2 => [
                'exists' => $row[11],
                'title' => $row[12],
                'type' => $row[13],
                'info_text' => $row[16],
                'info_links' => explode(',', $row[17]),
                'image_url' => $row[18],
                'image_text' => $row[19],
                'action_type' => explode(',', $row[14]),
                'action_desc' => $row[15]
            ],
            3 => [
                'exists' => $row[20],
                'title' => $row[21],
                'type' => $row[22],
                'info_text' => $row[27],
                'info_links' => explode(',', $row[28]),
                'image_url' => $row[25],
                'image_text' => $row[26],
                'action_type' => explode(',', $row[23]),
                'action_desc' => $row[24]
            ]
        ];
        return [
            'path_title' => $row[0],
            'prompt_title' => $row[1],
            'prompt_order' => $row[29],
            'prompt_segments' => $segments
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $service = $this->createService();
        } catch (\Exception $e) {
            $this->error($e);
            return;
        }

        $spreadsheetId = '14TYr1g6pBFIbg6Q-BOrlT4IVkHICS60MNuQxN7xx_50';
        $range = 'Prompts!B2:AE';
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();

        if (empty($values)) {
            $this->error("No data found.");
        } else foreach ($values as $row) {
            if (!array_key_exists(4, $row)) { break; }
            $promptInfo = $this->assemblePrompt($row);
            var_dump($promptInfo);
            break;
            // Print columns A and E, which correspond to indices 0 and 4.
            $this->info("adding $title ($categoryName: $difficulty)\n");

            $category = PathCategory::where('name', $categoryName)->first();
            $steps = $category->max * $category->span;
            $tags = explode(',', $tags);

            $newPath = PromptPath::firstOrNew([
                'path_title' => $title,
                'path_category' => $categoryName,
                'path_difficulty' => $difficulty
            ]);

            $newPath->path_thesis = $thesis;
            $newPath->tags = json_encode($tags);
            $newPath->steps = $steps;
            $newPath->category()->associate($category);
            $newPath->save();
        }
    }
}
