<?php

namespace App\Objects;

use Illuminate\Database\Eloquent\Model;

class FormElement extends Model
{
    protected $attributes = [ 'type' => '' ];

    public $types = [ 'button', 'checkboxes', 'datepicker', 'multi_static_select',
        'multi_external_select', 'multi_users_select', 'multi_conversations_select',
        'multi_channels_select', 'overflow', 'plain_text_input', 'radio_buttons',
        'static_select', 'external_select', 'users_select', 'conversations_select',
        'channels_select'
    ];

    public function setFormElementType($type)
    {
        if (!in_array($type, $this->types)) {
            error_log("No such form element type $type");
        }
        $this->attributes['type'] = $type;

        return;
    }

    public function getAttributes()
    {
        if ($this->attributes['type'] == '') {
            return $this->attributes;
        }
        switch($this->attributes['type']) {
            case 'button':
                break;
            case 'checkboxes':
                break;
            case 'datepicker':
                break;
            case 'multi_static_select':
                break;
            case 'multi_external_select':
                break;
            case 'multi_users_select':
                break;
            case 'multi_conversations_select':
                break;
            case 'multi_channels_select':
                break;
            case 'overflow':
                break;
            case 'plain_text_input':
                break;
            case 'radio_buttons':
                break;
            case 'static_select':
                break;
            case 'external_select':
                break;
            case 'users_select':
                break;
            case 'conversations_select':
                break;
            case 'channels_select':
                break;
        }
    }
}
