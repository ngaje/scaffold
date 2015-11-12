<?php
namespace Netshine\Scaffold\View\Form\FieldTypes;

use Netshine\Scaffold\View\Form\FieldBase;

class FieldYesOrNo extends FieldBase
{
    /** @var boolean **/
    public $is_nullable = false;

    public function setValue($value = null)
    {
        $this->value = intval($value) ? true : ($this->is_nullable && $value === null ? null : false);
    }
}