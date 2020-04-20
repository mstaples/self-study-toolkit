<?php

namespace App\Objects;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use phpDocumentor\Reflection\Types\Parent_;

class PromptSegment extends Model
{
    public $accessoryStruct = [
        'text' => [ 'type' => 'plain_text', 'text' => ''],
        'value' => '',
        'type' => '',
        'options' => [],
        'key' => []
    ];
    public $elements = [];
    public $key = [];

    protected $fillable = [
        'segment_title', 'segment_text', 'segment_type', 'segment_imageUrl', 'segment_url',
        'segment_accessory', 'segment_elements', 'segment_key', 'prompt_segment_order' ];
    protected $casts = [ 'segment_accessory' => 'array', 'segment_elements' => 'array', 'segment_key' => 'array' ];
    protected $attributes = [ 'segment_type' => 'section', 'prompt_segment_order' => 1 ];

    public function __construct(array $attributes = [])
    {
        $this->segment_accessory = $this->accessoryStruct;
        parent::__construct($attributes);
    }

    public function prompt()
    {
        return $this->belongsTo('App\Objects\Prompt');
    }

    public function createKey()
    {

    }

    public function getAccessory()
    {
        $accessory = $this->accessory;
        if (!$accessory) {
            $accessory = $this->accessoryStruct;
        }
        return $accessory;
    }

    public function getAccessoryOptionsString()
    {
        $accessory = $this->getAccessory();
        $options = $accessory['options'];
        $string = implode(',', $options);

        return strlen($string) < 2 ? '' : $string;
    }

    public function setPromptSegmentOrder($order)
    {
        Log::debug(__CLASS__.': '.__METHOD__.': with order = '.$order);
        if ($order == $this->prompt_segment_order) return;

        $options = $this->prompt->getSegmentOrderOptions();
        if (!array_key_exists($order, $options)) {
            Log::debug(__CLASS__.__METHOD__." provided invalid order option = $order");
            return;
        }
        $segments = $this->prompt->prompt_segments;
        foreach ($segments as $segment) {
            if ($segment->id == $this->id) {
                $segment->prompt_segment_order = $order;
                $segment->save();
                continue;
            }
            if ($segment->prompt_segment_order >= $order) {
                $segment->prompt_segment_order = $segment->prompt_segment_order + 1;
                $segment->save();
            }
        }
        return;
    }
    public function save($options = [])
    {
        Log::debug(__CLASS__." save()");
        Log::debug("title: ".$this->segment_title);
        Log::debug("order: ".$this->prompt_segment_order);
        parent::save($options);
    }

    public function getSegmentAnswersString()
    {
        $key = $this->key;
        $string = implode(',', $key);

        return strlen($string) < 2 ? '' : $string;
    }

    public function createAccessory($type, $options, $text, $value)
    {
        $accessory = $this->accessoryStruct;
        $accessory['type'] =  $type;
        switch($type) {
            case 'checkboxes': // deliberate cascade
            case 'radio_buttons':
                $accessory['options'] = [];
                foreach ($options as $option=>$key) {
                    $this->key[$option] = $key;
                    $accessory['options'][] = [
                        'value' => $option,
                        'text' => [
                            'type' => 'plain_text',
                            'text' => $option
                        ]
                    ];
                }
                break;
            case 'button':
                $accessory['text'] = $text;
                $accessory['value'] = $value;
                break;
        }
        return $accessory;
    }
}
