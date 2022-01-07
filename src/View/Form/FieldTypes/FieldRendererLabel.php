<?php
namespace Ngaje\Scaffold\View\Form\FieldTypes;

use Ngaje\Scaffold\View\Form\FieldRenderer;

class FieldRendererLabel extends FieldRenderer
{
    public function renderControl($type = null, $confirmation = false)
    {
        if (!$type) {
            $type = 'label';
        }
        ?>
        <div <?php $this->outputId(); ?> class="field-control fld-<?php echo $type; ?> <?php echo $this->field->css_class;?>" <?php $this->outputAttributes($this->field->attributes); ?>><?php echo $this->field->value; ?></div>
        <?php
        if ($this->field->clear_after) { ?>
            <div class="clear"></div>
            <?php
        }
    }
}
