<?php

namespace App\Traits;

use App\Objects\Knowledge;
use App\Objects\PathCategory;
use App\Objects\PromptPath;

trait PathTrait
{
    public function getInteractionOptions()
    {
        return [
            'none' => "none",
            'checkboxes' => 'Checkboxes',
            'radio_buttons' => 'Radio buttons',
            'button' => 'Button',
            'dropdown' => 'Select',
            'plain_text' => 'Text',
            'datepicker' => 'Date picker'
        ];
    }

    public function getPossibleStates()
    {
        return [
            'draft' => 'Saved progress, not ready for review.',
            'review' => 'Needs another teacher to proof read.',
            'trial' => 'Available to select and review by teachers, leaders, and mentors.',
            'live' => 'Available to all operators.'
        ];
    }

    public function getPerMax()
    {
        return [
            'Week' => [ 'min' => 5, 'max' => 7 ],
            'Month' => [ 'min' => 4, 'max' => 12 ],
            'Quarter' => [ 'min' => 6, 'max' => 36 ],
            'Year' => [ 'min' => 12, 'max' => 48 ]
        ];
    }

    public function getDepths()
    {
        return [ 'vague', 'passing', 'familiar', 'deep' ];
    }

    // single source of truth for these names
    public function getLevels()
    {
        return [
            'Basic' => "Basic prompts assume the operator is equipped with no more than a positive attitude.
                Steps will avoid requiring the operator to have specific knowledge of vocabulary, history, culture;
                and introduce that information in reinforcing but optional ways to facilitate overall advancement.",
            'Student' => "Student prompts assume the operator has, or is able to learn through their own initiative,
                sufficient knowledge of basic vocabulary, historical events, and cultural context to learn inclusive
                teamwork practices through self study, and work to facilitate that aim.",
            'Mentor' => "Mentor prompts assume the operator has, or is able to learn through their own initiative,
                sufficient knowledge of relevant vocabulary, history, and cultures to help others learn inclusive
                teamwork practices, and work to facilitate that aim.",
            'Leader' => "Leader prompts assume the operator has been a successful mentor, and has, or is able to
                learn through their own initiative, sufficient knowledge of relevant vocabulary, history, and
                cultures to help organizations make systemic changes towards inclusive teamwork, and work to
                facilitate that aim.",
            'Teacher' => "Teacher prompts assume the operator has been a successful leader, and has, or is able
                to learn through their own initiative, sufficient knowledge of relevant vocabulary, history,
                and cultures to create new prompt paths to facilitate people's journeys."
        ];
    }

    public function getCategories()
    {
        $categories = PathCategory::select(['name', 'id'])->get();
        $set = [];
        foreach ($categories as $category) {
            $set[$category->id] = $category->name;
        }
        return $set;
    }

    public function pathKnowledges()
    {
        $topics = [];
        foreach ($this->knowledges as $knowledge) {
            $topics[$knowledge->id] = $knowledge->name;
        }
        return $topics;
    }

    /*
     * returns [ id => name ] when markPathKnowledges = false
     * returns [ name => boolean ] when markPathKnowledges = true
     * */
    public function allKnowledges($markPrerequisites = true, $markPathKnowledges = false)
    {
        $all = Knowledge::all();
        $topics = [];
        foreach ($all as $each) {
            $name = $each->name;
            if ($markPrerequisites && $each->prerequisites) {
                $name = $name . "*";
            }
            if ($markPathKnowledges) {
                $topics[$each->name] = true;
                $has = $this->knowledges()->where('name', $each->name)->first();
                if (empty($has)) $topics[$each->name] = false;
                continue;
            }
            $topics[$each->id] = $name;
        }
        return $topics;
    }
}
