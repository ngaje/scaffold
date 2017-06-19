<?php
namespace Netshine\Scaffold\View\Form\FieldTypes;

use Netshine\Scaffold\View\Form\FieldBase;

class FieldYesOrNo extends FieldBase
{
    public function setValue($value = '')
    {
        $this->value = intval($value) ? true : false;
    }
}