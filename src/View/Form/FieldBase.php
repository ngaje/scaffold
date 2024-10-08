<?php
namespace Ngaje\Scaffold\View\Form;

use Ngaje\Scaffold\Language;
use Ngaje\Scaffold\Request;

/**
* @property mixed $value
*/
class FieldBase
{
    /** @var FieldRenderer **/
    public $renderer;
    /** @var FieldSet **/
    public $parent_field_set;
    /** @var string **/
    public $type;
    /** @var string **/
    public $name;
    /** @var string **/
    public $id;
    /** @var string **/
    public $css_class = '';
    /** @var array **/
    public $attributes = array();
    /** @var array **/
    public $container_attributes = array();
    /** @var array **/
    public $options = array();
    /** @var string **/
    public $caption = '';
    /** @var string **/
    public $help_text = '';
    /** @var boolean **/
    public $required = false;
    /** @var string **/
    public $error = '';
    /** @var boolean **/
    public $is_read_only = false;
    /** @var boolean **/
    public $request_confirmation = false;
    /** @var string Anything to output immediately before the control **/
    public $pre_control = '';
    /** @var string Anything to output immediately after the control **/
    public $post_control = '';
    /** @var string Anything to output immediately before the field (ie. before the caption) **/
    public $pre_field = '';
    /** @var string Anything to output immediately after the field (ie. after everything else has been output) **/
    public $post_field = '';
    /** @var boolean Whether or not the field is in use (present) on the form **/
    public $published = true;
    /** @var boolean Whether or not the field is visible on the form (can still be present, just hidden) **/
    public $visible = true;
    /** @var boolean **/
    public $clear_after = true;

    /** @var mixed Usually a string, but can be integer, date, or even array (in the case of a multi-select box) **/
    protected $value;
    /** @var mixed Confirmation of the value (if $request_confirmation is true) **/
    protected $confirm_value;
    /** @var Language **/
    protected $language;

    public function __construct(Language $language, $type, $name, $id, $value = '')
    {
        $this->language = $language;
        $this->type = $type;
        $this->name = $name;
        $this->id = $id;
        $this->value = $value;
        $this->initialise();
    }

    public function initialise() {}

    public function __get($property)
    {
        if ($property == 'value') {
            return $this->getValue();
        } else {
            throw new \Exception('Property or getter does not exist: ' . get_class($this) . '::' . $property);
        }
    }

    public function setRenderer(FieldRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function render()
    {
        if (isset($this->renderer)) {
            $this->renderer->render();
        }
    }

    public function setValue($value = '')
    {
        $this->value = str_replace('"', '&quot;', filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES));
    }

    public function setValueRaw($value = '')
    {
        $this->value = $value;
    }

    public function getValue($confirmation = false)
    {
        return $confirmation ? $this->confirm_value : $this->value;
    }

    public function setConfirmValue($value = '')
    {
        $this->confirm_value = str_replace('"', '&quot;', filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES));
    }

    public function setConfirmValueRaw($value = '')
    {
        $this->confirm_value = $value;
    }

    /**
    * @return boolean True on validation success, False on failure
    */
    public function validate(Request $request, &$message = null)
    {
        //Truncate if too long
        if (array_key_exists('maxlength', $this->attributes) && intval($this->attributes['maxlength']) > 0) {
            $this->value = substr($this->value, 0, intval($this->attributes['maxlength']));
        }

        if ($this->published && ($this->parent_field_set == null || $this->parent_field_set->published)) {
            //Ensure mandatory values are present
            if ($this->required && !$this->value) {
                $this->error = sprintf($this->language->form['err_fld_required'], $this->caption);
                return false;
            }

            //Request confirmation if applicable
            if ($this->request_confirmation) {
                if ($this->value != $this->confirm_value) {
                    $this->error = sprintf($this->language->form['err_fld_value_mismatch'], $this->caption);
                    return false;
                }
            }
        }

        return true;
    }

    /**
    * Perform any processing required for this field after it has passed validation (but before the entire
    * form is submitted). For example, this can be used to upload files to a staging area. If it is not OK to proceed
    * with form submission, return false, and give the reason in the $message output parameter
    * @param string $message Message to show on form (explaining why submission was halted)
    * @return boolean Whether or not processing can continue
    */
    public function process(Request $request, &$message)
    {
        return true;
    }

    /**
    * Perform any processing required for this field when the form is submitted. If it is not OK to proceed
    * with post-submission processing, return false, and give the reason in the $message output parameter
    * @param string $message Message to show on form (explaining why submission was halted)
    * @return boolean Whether or not processing can continue
    */
    public function formSubmitted(Request $request, &$message)
    {
        return true;
    }

    public function __toString()
    {
        return $this->getValue();
    }
}
