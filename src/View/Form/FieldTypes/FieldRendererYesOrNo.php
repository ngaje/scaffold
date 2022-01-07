<?php
namespace Ngaje\Scaffold\View\Form\FieldTypes;

use Ngaje\Scaffold\View\Form\FieldRenderer;

class FieldRendererYesOrNo extends FieldRenderer
{
    protected function renderCaption($confirmation = false)
    {
        //Use a div tag instead of a label, as we will be using labels for the individual options
        ob_start();
        parent::renderCaption($confirmation);
        $output = ob_get_clean();
        $output = str_replace('<label', '<div', $output);
        $output = str_replace('</label>', '</div>', $output);
        echo $output;
    }

    public function renderControl($type = null, $confirmation = false)
    {
        ?>
        <input type="radio" name="<?php echo $this->field->name; ?>" <?php $this->outputId(null, '0'); ?> value="0"<?php if ($this->field->getValue($confirmation) == false) {echo ' checked="checked"';} ?> class="field-control-radio <?php echo $this->field->css_class; ?>"<?php $this->outputAttributes($this->field->attributes); ?>><label class="radio-label" for="<?php echo $this->field->name; ?>_0"><?php echo $this->language->scaffold['negative']; ?></label>
        <input type="radio" name="<?php echo $this->field->name; ?>" <?php $this->outputId(null, '1'); ?> value="1"<?php if ($this->field->getValue($confirmation)) {echo ' checked="checked"';} ?> class="field-control-radio <?php echo $this->field->css_class; ?>"<?php $this->outputAttributes($this->field->attributes); ?>><label class="radio-label" for="<?php echo $this->field->name; ?>_1"><?php echo $this->language->scaffold['affirmative']; ?></label>
        <?php
    }
}
