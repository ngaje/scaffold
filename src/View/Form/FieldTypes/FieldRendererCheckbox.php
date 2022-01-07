<?php
namespace Ngaje\Scaffold\View\Form\FieldTypes;

use Ngaje\Scaffold\View\Form\FieldRenderer;

class FieldRendererCheckbox extends FieldRenderer
{
    public function render()
    {
        if ($this->field->published) {
            $this->loadJsFiles();
            $this->preRender();
            $this->renderErrorMessage();
            $this->renderPreControl();
            $this->renderControl();
            $this->renderPostControl();
            $this->renderCaption();
            if ($this->field->request_confirmation) {
                $this->renderPreControl(true);
                $this->renderControl(null, true);
                $this->renderPostControl(true);
                $this->renderCaption(true);
            }
            $this->renderHelpText();
            $this->postRender();
        }
    }

    protected function renderControl($type = null, $confirmation = false)
    {
        if ($this->field->value) {
            $this->field->attributes['checked'] = 'checked';
        }
        parent::renderControl($type, $confirmation);
    }
}
