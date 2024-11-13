<?php
namespace Ngaje\Scaffold\View\Form\FieldTypes;

use Ngaje\Scaffold\View\Form\FieldRenderer;

class FieldRendererFile extends FieldRenderer
{
    public function renderControl($type = null, $confirmation = false)
    {
        if ($this->field->getUploadFolder() !== false) {
            $file_name = $this->field->getValue() ? htmlentities($this->field->getValue()) : null;
            if ($file_name && strlen($file_name) > 0) {
                //File already uploaded to either final destination or staging area - offer to delete
                echo $file_name;
                ?>
                <input type="hidden" name="<?php echo $this->field->name; ?>" value="<?php echo $file_name; ?>" />
                <input type="submit" name="delete_<?php echo $this->field->name; ?>" value="<?php echo $this->language->form['delete']; ?>" />
                <?php
            } else {
                parent::renderControl();
            }
            ?>
            <input type="hidden" name="orig_<?php echo $this->field->name; ?>" value="<?php echo implode(",", $this->field->orig_file_names); ?>" />
            <?php
        } else {
            echo $this->language->form['err_fld_file_no_destination'];
        }
    }
}
