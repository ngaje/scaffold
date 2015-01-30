<?php
namespace Netshine\Scaffold\View\Form\FieldTypes;

use Netshine\Scaffold\View\Form\FieldRenderer;

class FieldRendererButton extends FieldRenderer
{
    protected function renderControl($type = null, $confirmation = false)
    {
        if (!$type) {
            $type = $this->field->type;
        }
        ?>
        <button name="<?php echo ($confirmation ? 'confirm_' : '') . $this->field->name; ?>" class="field-control fld-<?php echo $this->field->type; ?> <?php echo $this->field->css_class; ?>" <?php $this->outputId($confirmation ? 'confirm_' : ''); $this->outputAttributes($this->field->attributes); ?>>
        <?php if ($this->field->getValue($confirmation) != null) {echo $this->field->getValue($confirmation);} ?>
        </button>
        <?php
    }
}