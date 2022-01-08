<?php
namespace Ngaje\Scaffold\View\Form;

use Ngaje\Scaffold\Language;
use Ngaje\Scaffold\ICms;
use Ngaje\Scaffold\Url;
use Ngaje\Scaffold\View\Form\FieldTypes\FieldText;

class FieldFactory
{
    /** @var Language **/
    protected $language;
    /** @var string **/
    protected $field_types_namespace;
    /** @var ICms **/
    protected $cms;

    public function __construct(Language $language, $field_types_namespace, ICms $cms)
    {
        $this->language = $language;
        $this->field_types_namespace = $field_types_namespace;
        $this->cms = $cms;
    }

    /**
    * @param string $form_resource
    * @param string $form_method
    * @param string $name
    * @param string $id
    * @return FormBase
    */
    public function createForm($form_resource = '', $form_method = 'post', $name = '', $id = '')
    {
        $form = new FormBase($this, $this->cms, $this->language, $form_resource, $form_method, $name, $id);
        $form_renderer = new FormRenderer($form, new Url());
        $form->renderer = $form_renderer;
        return $form;
    }

    /**
    * @param string $legend
    * @param string $id
    * @return FieldSet
    */
    public function createFieldSet(FormBase $form = null, $legend = '', $id = '')
    {
        $field_set = new FieldSet($legend, $id);
        $field_set->parent_form = $form;
        return $field_set;
    }

    /**
    * @param string $type
    * @param string $name
    * @param string $id
    * @return FieldBase
    */
    public function createField($type, $name, FieldSet $field_set = null, $id = '')
    {
        $field_type = str_replace(' ', '', ucwords(str_replace('_', ' ', $type)));
        $class_name = $this->field_types_namespace . 'Field' . $field_type;
        if (!class_exists($class_name)) {
            $class_name = 'Ngaje\Scaffold\View\Form\FieldTypes\Field' . $field_type;
        }
        if (class_exists($class_name)) {
            $reflection_class = new \ReflectionClass($class_name);
            if ($reflection_class->isSubclassOf('Ngaje\Scaffold\View\Form\FieldBase')) {
                $field = new $class_name($this->language, $type, $name, $id);
                $field_renderer = $this->createFieldRenderer($field_type, $field);
                return $field;
            }
        }

        //If we can't find it, default to a text box
        $field = new FieldBase($this->language, $type, $name, $id);
        $field_renderer = $this->createFieldRenderer($field_type, $field);

        $field->parent_field_set = $field_set;

        return $field;
    }

    public function createFieldRenderer($field_type, FieldBase $field)
    {
        $class_name = $this->field_types_namespace . 'FieldRenderer' . $field_type;
        if (!class_exists($class_name)) {
            $class_name = 'Ngaje\Scaffold\View\Form\FieldTypes\FieldRenderer' . $field_type;
        }

        if (class_exists($class_name)) {
            return new $class_name($field, $this->language, $this->cms);
        } else {
            return new FieldRenderer($field, $this->language, $this->cms);
        }
    }
}
