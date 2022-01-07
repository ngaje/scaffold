<?php
namespace Ngaje\Scaffold\View\Form\FieldTypes;

use Ngaje\Scaffold\View\Form\FieldRenderer;

class FieldRendererTextarea extends FieldRenderer
{
    public function renderControl($type = null, $confirmation = false)
    {
        ?>
        <textarea name="<?php echo $this->field->name; ?>" <?php $this->outputId(); $this->outputAttributes($this->field->attributes); ?> class="field-control fld-html <?php echo $this->field->css_class; ?>"><?php echo $this->field->value; ?></textarea>
        <?php
    }
}
