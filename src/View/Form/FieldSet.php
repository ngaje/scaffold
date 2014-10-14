<?php
namespace Netshine\Scaffold\View\Form;

class FieldSet
{
    /** @var FormBase **/
    public $parent_form;
    /** @var string **/
    public $legend;
    /** @var string **/
    public $id;
    /** @var string **/
    public $css_class;
    /** @var string HTML tag to use for the fieldset (typically either div or fieldset) **/
    public $fieldset_tag = 'fieldset';
    /** @var array **/
    public $fields = array();
    /** @var boolean **/
    public $published = true;

    public function __construct($legend = '', $id = '')
    {
        $this->legend = $legend;
        $this->id = $id;
    }

    public function addField(FieldBase $field)
    {
        $field->parent_field_set = $this;
        $this->fields[] = $field;
    }

    public function fieldExists($name)
    {
        foreach ($this->fields as $field)
        {
            if ($field->name == $name) {
                return true;
            }
        }
        return false;
    }

    public function &getField($field_name, $include_unpublished = false)
    {
        foreach ($this->fields as $field) {
            if ($field->name == $field_name && $field->published) {
                return $field;
            }
            if ($field->type == 'container' && $field->published) {
                $field_set = $field->field_set;
                $child_field = $field_set->getField($field_name, false);
                if ($child_field) {
                    return $child_field;
                }
            }
        }
        if ($include_unpublished) {
            reset($this->fields);
            unset($field);
            foreach ($this->fields as $field)
            {
                if ($field->name == $field_name) {
                    return $field;
                }
                if ($field->type == 'container') {
                    $field_set = $field->field_set;
                    $child_field = $field_set->getField($field_name, true);
                    if ($child_field) {
                        return $child_field;
                    }
                }
            }
        }
        $result = null; //Have to return by reference
        return $result;
    }

    public function render()
    {
        if ($this->published) {
            $this->parent_form->renderer->renderFieldSet($this);
        }
    }
}