<?php
namespace Netshine\Scaffold\View\Form\FieldTypes;

use Netshine\Scaffold\View\Form\FieldRenderer;

class FieldRendererSelectDiv extends FieldRenderer
{
    public function renderControl($type = null, $confirmation = false)
    {
        ?>
        <div class="select-div" onclick="" style="width:200px;">
        <select name="<?php echo $this->field->name; ?>" class="field-control <?php echo $this->field->type; ?> <?php echo $this->field->css_class; ?>" <?php $this->outputId(); $this->outputAttributes($this->field->attributes); ?>>
            <?php
            foreach ($this->field->options as $value=>$description)
            {
                ?>
                <option value="<?php echo $value; ?>"<?php if (substr(strtolower($value),0,9)==='separator'){echo ' disabled="disabled"';} else {if ($this->field->getValue() && $value == $this->field->getValue()){echo ' selected="selected"';}} ?>><?php echo substr(strtolower($value),0,9)==='separator' ? str_repeat($description, 10) : $description; ?></option>
                <?php
            }
            ?>
        </select>
        </div>
        <?php
    }
}