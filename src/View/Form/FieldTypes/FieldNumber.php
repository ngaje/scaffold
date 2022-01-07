<?php
namespace Ngaje\Scaffold\View\Form\FieldTypes;

use Ngaje\Scaffold\View\Form\FieldBase;

class FieldNumber extends FieldBase
{
    public $allow_decimals = false;
    public $min_value = 0;
    public $max_value = 100;
    public $step = 1;
}
