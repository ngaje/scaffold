<?php
namespace Netshine\Scaffold\View\Form\FieldTypes;

use Netshine\Scaffold\View\Form\FieldRenderer;

class FieldRendererSelect extends FieldRenderer
{
    public function renderControl($type = null, $confirmation = false)
    {
        ?>
        <select name="<?php echo $this->field->name; ?>" class="field-control <?php echo $this->field->type; ?> <?php echo $this->field->css_class; ?>" <?php $this->outputId(); $this->outputAttributes($this->field->attributes); ?>>
            <?php
            foreach ($this->field->options as $value=>$description)
            {
                ?>
                <option value="<?php echo $value; ?>"<?php if (strtolower($value)==='separator'){echo ' disabled="disabled"';} else {if ($this->field->getValue() && $value == $this->field->getValue()){echo ' selected="selected"';}} ?>><?php echo strtolower($value)==='separator' ? str_repeat($description, 10) : $description; ?></option>
                <?php
            }
            ?>
        </select>
        <?php
    }
}