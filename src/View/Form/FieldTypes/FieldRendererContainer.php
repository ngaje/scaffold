<?php
namespace Netshine\Scaffold\View\Form\FieldTypes;

use Netshine\Scaffold\View\Form\FieldRenderer;

class FieldRendererContainer extends FieldRenderer
{
    public function preRender()
    {
        parent::preRender()
        ?>
        <div <?php $this->outputId(); ?> class="field-control fld-container <?php echo $this->field->css_class; ?>"><?php echo $this->field->value; ?>
        <?php
    }

    public function renderControl($type = null, $confirmation = false)
    {
        if (!$this->field->field_set->id) {
            $this->field->field_set->id = uniqid(); //Otherwise it will try to render all the fieldsets on the form and get stuck in a loop
        }
        $this->field->field_set->render();
        ?>
        <div class="clear"></div>
        <?php
    }

    public function postRender()
    {
        ?></div><?php
        parent::postRender();
    }
}