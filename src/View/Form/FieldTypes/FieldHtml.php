<?php
namespace Netshine\Scaffold\View\Form\FieldTypes;

use Netshine\Scaffold\View\Form\FieldBase;

class FieldHtml extends FieldBase
{
    public function setValue($value = '')
    {
        $this->value = $value;
    }
}