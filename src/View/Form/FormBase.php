<?php
namespace Netshine\Scaffold\View\Form;

use Netshine\Scaffold\ICms;
use Netshine\Scaffold\Language;
use Netshine\Scaffold;

abstract class FormBase
{
    /** @var FieldFactory **/
    protected $factory;
    /** @var ICms **/
    protected $cms;
    /** @var Language **/
    protected $language;
    /** @var boolean **/
    protected $initialised = false;

    /** @var FormRenderer **/
    public $renderer;
    /** @var string **/
    public $name;
    /** @var string **/
    public $id;
    /** @var string **/
    public $css_class = 'form';
    /** @var string **/
    public $form_resource;
    /** @var string **/
    public $form_method;
    /** @var array **/
    public $field_sets = array();
    /** @var boolean **/
    public $show_mandatory_key = true;
    /** @var string **/
    public $error_message = '';
    /** @var string **/
    public $message = '';
    /** @var string Unique idenitifer for each form session **/
    public $submission_id;
    /** @var int **/
    public $record_id;
    /** @var FieldSet Fields to use for filtering records (name and id must start with 'filter_') **/
    public $filter_fields;
    /** @var string **/
    public $return_url;

    /** @var array $preserved_filters Any filters that need to be preserved between page requests (so we can return to a list in the same state we left it) **/
    public $preserved_filters = array();

    use Scaffold\TResource;

    function __construct(FieldFactory $factory, ICms $cms, Language $language, $form_resource, $form_method, $name = '', $id = '')
    {
        $this->factory = $factory;
        $this->cms = $cms;
        $this->language = $language;
        $this->form_resource = $form_resource;
        $this->form_method = $form_method;
        $this->name = $name ? $name : $form_resource;
        $this->id = $id ? $id : $this->name;
        $submission_id = htmlentities(@$_REQUEST['submission_id']);
        $this->submission_id = strlen($submission_id) == 13 ? $submission_id : uniqid();
    }

    public function setRenderer(FormRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function render()
    {
        if (!$this->initialised) {
            $this->initialise();
        }
        if (isset($this->renderer)) {
            $this->renderer->render();
        }
    }

    protected function initialise()
    {
        $this->initialised = true;
    }

    public function addFieldSet(FieldSet $field_set)
    {
        $field_set->parent_form = $this;
        if ($field_set->id) {
            $this->field_sets[$field_set->id] = $field_set;
        } else {
            $this->field_sets[] = $field_set;
        }
    }

    public function fieldsInError()
    {
        foreach ($this->field_sets as $field_set)
        {
            foreach ($field_set->fields as $field)
            {
                if (strlen($field->error) > 0) {
                    return true;
                }
            }
        }
        return false;
    }

    public function containsFileUploads()
    {
        foreach ($this->field_sets as $field_set)
        {
            foreach ($field_set->fields as $field)
            {
                if ($field instanceof FieldTypes\FieldFile) {
                    return true;
                }
            }
        }
        return false;
    }

    public function fieldExists($field_name, $include_unpublished = false)
    {
        foreach ($this->field_sets as $field_set)
        {
            if ($field_set->published) {
                foreach ($field_set->fields as $field)
                {
                    if ($field->published && $field->name == $field_name) {
                        return true;
                    }
                }
            }
        }
        if ($include_unpublished) {
            reset($this->field_sets);
            unset($field_set);
            unset($field);
            foreach ($this->field_sets as $field_set)
            {
                foreach ($field_set->fields as $field)
                {
                    if ($field->name == $field_name) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /** @return FieldBase **/
    public function &getField($field_name, $include_unpublished = false)
    {
        foreach ($this->field_sets as &$field_set)
        {
            if ($field_set->published) {
                foreach ($field_set->fields as &$field)
                {
                    if ($field->name == $field_name && $field->published) {
                        return $field;
                    }
                }
            }
        }

        if ($include_unpublished) {
            reset($this->field_sets);
            unset($field_set);
            unset($field);
            foreach ($this->field_sets as &$field_set)
            {
                foreach ($field_set->fields as &$field)
                {
                    if ($field->name == $field_name) {
                        return $field;
                    }
                }
            }
        }

        $result = null; //Have to return by reference
        return $result;
    }

    public function createField($type, $name, FieldSet $field_set = null, $caption = null, $help = null, $value = null, $required = false, $maxlength = 0, $attributes = array(), $id = '', $options = array())
    {
        $field = $this->factory->createField($type, $name, $field_set, strlen($id) > 0 ? $id : $name);

        $caption = $caption === null ? $this->getString($name) : $caption;
        $help = $help === null ? $this->getString($name . '_help') : $help;
        $attributes = $attributes === null ? array() : $attributes;

        $field->caption = $caption === null ? '' : $caption;
        if ($maxlength) {
            $attributes['maxlength'] = $maxlength;
        }
        if ($value !== null) {
            $field->setValue($value);
        }
        $field->required = $required;
        $field->attributes = array_merge($field->attributes, $attributes);
        $field->options = array_merge($field->options, $options);
        $field->help_text = $help === null ? '' : $help;
        return $field;
    }

    public function submit($suppress_errors = false)
    {
        $success = true;
        if ($this->validate($suppress_errors)) {
            foreach ($this->field_sets as $field_set)
            {
                foreach ($field_set->fields as $field)
                {
                    if (!$field->formSubmitted($this->message)) {
                        if ($suppress_errors) {
                            $field->error = '';
                        }
                        $success = false;
                    }
                }
            }
            return $success;
        }
    }

    /**
    * @return array Return array of errors (or empty array if all ok)
    */
    public function validate($suppress_errors = false)
    {
        $success = true;
        foreach ($this->field_sets as $field_set)
        {
            foreach ($field_set->fields as &$field)
            {
                $valid = $field->validate($suppress_errors);
                if ($valid) {
                    $success = $valid ? $success : false;
                    $field->process($this->message);
                } else {
                    $success = false;
                    if ($suppress_errors) {
                        $field->error = '';
                    }
                }
            }
        }
        if (!$success) {
            $this->error_message = $this->getString('errors_present', 'form');
        } else if (strlen($this->message) > 0) {
            return false; //Valid but submission should not continue as there is some information to display
        }
        return $success;
    }
}