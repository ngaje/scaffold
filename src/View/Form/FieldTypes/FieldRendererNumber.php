<?php
namespace Ngaje\Scaffold\View\Form\FieldTypes;

use Ngaje\Scaffold\View\Form\FieldRenderer;
use Ngaje\Scaffold\View\Form\FieldTypes\FieldNumber;

class FieldRendererNumber extends FieldRenderer
{
    /** @var FieldNumber **/
    protected $field;

    public function renderControl($type = 'number', $confirmation = false)
    {
        $this->field->attributes['min'] = $this->field->min_value;
        $this->field->attributes['max'] = $this->field->max_value;
        $this->field->attributes['step'] = $this->field->step;
        parent::renderControl($type, $confirmation);
    }
}
