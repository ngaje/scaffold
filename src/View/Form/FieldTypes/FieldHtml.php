<?php
namespace Ngaje\Scaffold\View\Form\FieldTypes;

use Ngaje\Scaffold\View\Form\FieldBase;

class FieldHtml extends FieldBase
{
    public function setValue($value = '')
    {
        $this->value = $value;
    }
}
