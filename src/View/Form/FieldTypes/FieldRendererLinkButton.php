<?php
namespace Netshine\Scaffold\View\Form\FieldTypes;

use Netshine\Scaffold\View\Form\FieldRenderer;

class FieldRendererLinkButton extends FieldRenderer
{
    protected function renderCaption($confirmation = false)
    {
        if (!$this->field->image) {
            parent::renderCaption($confirmation);
        }
    }

    public function renderControl($type = null, $confirmation = false)
    {
        if (strpos($this->field->url, '[return]') !== false) {
            $this->field->url = str_replace('[return]', $this->field->parent_field_set->parent_form->return_url, $this->field->url);
        }
        if ($this->field->url) {
            $this->field->attributes['onclick']='window.location=\'' . $this->field->url . '\';return false;';
        }
        if ($this->field->image) {
            ?>
            <a href="<?php echo $this->field->url; ?>"class="field-control fld-<?php echo $this->field->type; ?> title="<?php echo $this->field->value; ?>" <?php echo $this->field->css_class; ?>" <?php $this->outputId($confirmation ? 'confirm_' : ''); $this->outputAttributes($this->field->attributes); ?>>
                <img src="<?php echo $this->field->image; ?>" alt="<?php echo $this->field->value; ?>" border="0" />
                <?php
                if ($this->field->caption) {
                    $caption = $confirmation ? sprintf($this->language->form['fld_confirm_caption'], $this->field->caption) : $this->field->caption;
                    echo $caption;
                }
                 ?>
            </a>
            <?php
        } else {
            parent::renderControl('submit');
        }
    }
}