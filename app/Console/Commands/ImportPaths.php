<?php

namespace App\Console\Commands;

use App\Objects\PathCategory;
use App\Objects\PromptPath;
use App\Traits\GoogleSheetsApiTrait;
use Illuminate\Console\Command;

class ImportPaths extends Command
{
    use GoogleSheetsApiTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:paths';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import prompt paths from a google spreadsheet';

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
        try {
            $service = $this->createService();
        } catch (\Exception $e) {
            $this->error($e);
            return;
        }

        $spreadsheetId = '14TYr1g6pBFIbg6Q-BOrlT4IVkHICS60MNuQxN7xx_50';
        $range = 'Prompt Paths!B2:FE';
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();

        if (empty($values)) {
            $this->error("No data found.");
        } else foreach ($values as $row) {
            if (!array_key_exists(4, $row)) { break; }
            $level = $row[0];
            $categoryName = $row[1];
            $title = $row[2];
            $thesis = $row[3];
            $knowledges = $row[4];
            // Print columns A and E, which correspond to indices 0 and 4.
            $this->info("adding $title ($categoryName: $level)\n");

            $category = PathCategory::where('name', $categoryName)->first();
            $steps = $category->max * $category->span;
            $knowledges = explode(',', $knowledges);

            $newPath = PromptPath::firstOrNew([
                'path_title' => $title,
                'path_category' => $categoryName,
                'path_level' => $level
            ]);

            $newPath->path_thesis = $thesis;
            $newPath->steps = $steps;
            $newPath->category()->associate($category);
            $newPath->save();

            foreach ($knowledges as $knowledge) {
                $know = DB::table('knowledges')->where('name', $knowledge)->first();
                if ($know) {
                    $know->paths()->attach($newPath);
                }
            }
        }
    }
}
