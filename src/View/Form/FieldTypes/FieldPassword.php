<?php
namespace Netshine\Scaffold\View\Form\FieldTypes;

use Netshine\Scaffold\View\Form\FieldBase;

class FieldPassword extends FieldBase
{
    /** @var int $mask_length Optionally show a dummy string of the specified length in place of the actual password (if the form is posted back with the dummy string intact, we can assume the password should be left as it is) **/
    public $mask_length = 0;
    /** @var boolean **/
    public $request_confirmation = true;

    public function initialise()
    {
        $this->attributes['onfocus']='this.select();';
        $this->attributes['onmouseup']='return false;';
    }

    public function getValue($confirmation = false)
    {
        if (!$this->value && $this->mask_length > 0) {
            return str_pad('', $this->mask_length, '*');
        } else {
            return parent::getValue($confirmation);
        }
    }

    public function validate(&$message = null)
    {
        if (parent::validate($message)) {
            if (strlen($this->value) >= 8) {
                if ($this->value == str_pad('', strlen($this->value), '*') || preg_match('/^(?=.*\d)(?=.*[A-Za-z])[0-9A-Za-z!@#$%]{8,30}$/', $this->value)) {
                    return true;
                }
            }
            $this->error = $this->language->form['err_fld_password_strength'];
        }
        return false;
    }
}