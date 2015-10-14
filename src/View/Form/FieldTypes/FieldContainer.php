<?php
namespace Netshine\Scaffold\View\Form\FieldTypes;

use Netshine\Scaffold\View\Form\FieldSet;
use Netshine\Scaffold\Request;

class FieldContainer extends FieldLabel
{
    /** @var FieldSet **/
    public $field_set;

    public function process(Request $request, &$message)
    {
        $ret_val = true;
        foreach ($this->field_set->fields as $field) {
            $ret_val = !$ret_val ? false : $field->process($request, $message);
        }
        return $ret_val;
    }

    public function validate(Request $request, &$message = null, $suppress_errors = false)
    {
        $ret_val = true;
        foreach ($this->field_set->fields as $field) {
            $ret_val = !$ret_val ? false : $field->validate($request, $message, $suppress_errors);
        }
        return $ret_val;
    }

    public function formSubmitted(Request $request, &$message)
    {
        $ret_val = true;
        foreach ($this->field_set->fields as $field) {
            $ret_val = !$ret_val ? false : $field->formSubmitted($request, $message);
        }
        return $ret_val;
    }
}