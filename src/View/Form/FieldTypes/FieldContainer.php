<?php
namespace Netshine\Scaffold\View\Form\FieldTypes;

use Netshine\Scaffold\View\Form\FieldSet;

class FieldContainer extends FieldLabel
{
    /** @var FieldSet **/
    public $field_set;

    public function process(&$message)
    {
        $ret_val = true;
        foreach ($this->field_set->fields as $field) {
            $ret_val = !$ret_val ? false : $field->process($message);
        }
        return $ret_val;
    }

    public function validate(&$message = null)
    {
        $ret_val = true;
        foreach ($this->field_set->fields as $field) {
            $ret_val = !$ret_val ? false : $field->validate($message);
        }
        return $ret_val;
    }

    public function formSubmitted(&$message)
    {
        $ret_val = true;
        foreach ($this->field_set->fields as $field) {
            $ret_val = !$ret_val ? false : $field->formSubmitted($message);
        }
        return $ret_val;
    }
}