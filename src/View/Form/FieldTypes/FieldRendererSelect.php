<?php
namespace Ngaje\Scaffold\View\Form\FieldTypes;

use Ngaje\Scaffold\View\Form\FieldRenderer;

class FieldRendererSelect extends FieldRenderer
{
    public function renderControl($type = null, $confirmation = false)
    {
        ?>
        <select name="<?php echo $this->field->name; ?>" class="field-control <?php echo $this->field->type; ?> <?php echo $this->field->css_class; ?>" <?php $this->outputId(); $this->outputAttributes($this->field->attributes); ?>>
        <?php
        if($this->field->attributes && in_array('multiple', $this->field->attributes)) {
            $field_name = $this->field->name;
            if (stripos($field_name, '[]') !== false) {
                $field_name = substr($field_name, 0, -2);
                if (isset($_REQUEST[$field_name])) {
                    $values = $_REQUEST[$field_name];
                    if (is_array($values)) {
                        $this->field->setValueRaw($values);
                    }
                }
            }
        }

        $selected_value = $this->field->getValue();

        foreach ($this->field->options as $value=>$description)
        {
            $selected = $selected_value && (is_array($selected_value) ? in_array($value,$selected_value) : $value == $selected_value);
            ?>
            <option value="<?php echo $value; ?>"<?php if (substr(strtolower($value),0,9)==='separator' || substr(strtolower($value),0,9)==='disabled_'){echo ' disabled="disabled"';} else {if ($selected){echo ' selected="selected"';}} ?>><?php echo substr(strtolower($value),0,9)==='separator' ? str_repeat($description, 10) : $description; ?></option>
            <?php
        }
        ?>
        </select>
        <?php
    }
}
