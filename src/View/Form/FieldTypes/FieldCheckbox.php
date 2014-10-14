<?php
namespace Netshine\Scaffold\View\Form\FieldTypes;

use Netshine\Scaffold\View\Form\FieldBase;

class FieldCheckbox extends FieldBase
{
    public function setValue($value = '')
    {
        $this->value = $value ? true : false;
    }
}