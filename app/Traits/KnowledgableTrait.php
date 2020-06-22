<?php

namespace App\Traits;

use App\Objects\Knowledge;
use App\Objects\User;
use Illuminate\Support\Facades\Log;

trait KnowledgableTrait
{
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
}
