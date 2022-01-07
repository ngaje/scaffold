<?php
namespace Ngaje\Scaffold\View\Form\FieldTypes;

use Ngaje\Scaffold\View\Form\FieldBase;

class FieldLabel extends FieldBase
{
    public $clear_after = true;

    public function initialise()
    {
        $this->is_read_only = true;
    }
}
