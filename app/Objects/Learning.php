<?php

namespace App\Objects;

use Illuminate\Database\Eloquent\Model;

class Learning extends Model
{

    protected $fillable = [ 'operator_id', 'knowledge_id', 'paths_complete', 'feedback_received', 'questions_answered', 'depth', 'level', 'last_evaluation_completed' ];

    protected $attributes = [
        'paths_complete' => 0,
        'feedback_received' => 0,
        'questions_answered' => 0,
        'depth' => 'vague',
        'level' => 'basic'
    ];

    public function knowledge()
    {
        return $this->belongsTo('App\Objects\Knowledge');
    }

    public function operator()
    {
        return $this->belongsTo('App\Objects\Operator');
    }

    public function travels()
    {
        return $this->belongsToMany('App\Objects\Travel', 'learnings_travels', 'learning_id', 'travel_id');
    }

    public function answers()
    {
        return $this->belongsToMany('App\Objects\SamplingAnswer', 'learnings_answers', 'answer_id', 'learning_id');
    }

    public function feedback_records()
    {
        return $this->belongsToMany('App\Objects\FeedbackRecord');
    }

    public function sortAndTallyAnswers()
    {
        $operator = $this->operator;
        $tally = [ 'vague' => 0, 'passing' => 0, 'familiar' => 0, 'deep' => 0 ];

        $this->questions_answered = $operator->sampling_questions_count;
        if ($this->questions_answered < 1) {
            return $tally;
        }
        $answers = $this->answers;

        foreach ($answers as $answer) {
            $depth = strtolower($answer->depth);
            $impact = $answer->correct ? 1 : -1;
            $tally[$depth] += $impact;
        }
        return $tally;
    }

    public function sortAndTallyTravels()
    {
        $tally = [ 'basic' => 0, 'student' => 0, 'leader' => 0, 'teacher' => 0 ];

        $travels = $this->travels;

        foreach ($travels as $travel) {
            $level = strtolower($travel->level);
            $tally[$level]++;
        }
        return $tally;
    }

    public function evaluateKnowledge()
    {
        $last = (int) $this->last_evaluation_completed;
        $last = $last / 60;
        $now = (int) time();
        if ($now - $last < 720) {
            return $this->level;
        }
        // basic, student, leader, teacher
        $travels = $this->sortAndTallyTravels();
        // vague, passing, familiar, deep
        $answers = $this->sortAndTallyAnswers();
        // $this->feedback

        $depthLevelMapping = [
            'vague' => 'basic',
            'passing' => 'student',
            'familiar' => 'leader',
            'deep' => 'teacher'
        ];

        foreach ($answers as $level => $answer) {
            if ($answer < 1) {
                $this->level = $depthLevelMapping[$level];
                break;
            }
            $count = false;
            $travelImpact = 0;
            foreach ($travels as $challenge => $tally) {
                if ($challenge == $depthLevelMapping[$level]) $count = true;
                if ($count) $travelImpact += $tally;
            }
            if ($answer + $travelImpact < 4 || $level == array_key_last($answers)) {
                $this->level = $depthLevelMapping[$level];
                break;
            }
        }
        $this->last_evaluation_completed = time();
        $this->save();
        return $this->level;
    }
}
