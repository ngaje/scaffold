<?php
namespace Ngaje\Scaffold\View\Form\FieldTypes;

use Ngaje\Scaffold\View\Form\FieldBase;

class FieldYesOrNo extends FieldBase
{
    public function setValue($value = '')
    {
        $this->value = intval($value) ? true : false;
    }
}
