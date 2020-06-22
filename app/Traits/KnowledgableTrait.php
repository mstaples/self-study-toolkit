<?php

namespace App\Traits;

use App\Objects\Knowledge;
use App\Objects\User;
use Illuminate\Support\Facades\Log;

trait KnowledgableTrait
{

    /*
     * returns [ name => name ] when markKnown = false
     * returns [ name => boolean ] when markKnown = true
     * */
    public function allKnowledges($markPrerequisites = true, $markKnown = false)
    {
        $all = Knowledge::all();
        $topics = [];
        foreach ($all as $each) {
            $name = $each->name;
            if ($markPrerequisites && $each->prerequisites) {
                $name = $name . "*";
            }
            if ($markKnown) {
                $topics[$each->name] = true;
                $has = $this->knowledges()->where('name', $each->name)->first();
                if (empty($has)) $topics[$each->name] = false;
                continue;
            }
            $topics[$each->name] = $name;
        }
        return $topics;
    }

    public function hasKnowledge($knowledge)
    {
        $has = $this->knowledges()->where('name',$knowledge)->first();
        return empty($has) ? false : true;
    }

    public function getCurrentKnowledges()
    {
        $current = [];
        $knowledges = Knowledge::all();
        foreach ($knowledges as $knowledge) {
            $name = $knowledge->name;
            $current[$name] = $this->hasKnowledge($name);
        }
        return $current;
    }

    public function getDefaultTopics()
    {
        $topics = Knowledge::where('prerequisites', false)->inRandomOrder()->take(12)->get('name');
        $set = [];
        foreach ($topics as $topic) {
            $set[$topic->name] = $topic->name;
        }

        return $set;
    }
}
