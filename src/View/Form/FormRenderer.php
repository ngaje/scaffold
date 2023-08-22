<?php
namespace Ngaje\Scaffold\View\Form;

use Ngaje\Scaffold\Url;

class FormRenderer
{
    /** @var FormBase **/
    protected $form;
    /** @var Url **/
    protected $site_url;

    protected $form_started = false;

    public function __construct(FormBase $form, Url $site_url)
    {
        $this->form = $form;
        $this->form->setRenderer($this);
        $this->site_url = $site_url;
    }

    public function render()
    {
        $this->startForm();
        $this->renderFieldSets();
        $this->endForm();
    }

    public function startForm()
    {
        if (!$this->form_started) {
            $this->openFormTag();
            $this->outputHiddenFields();
            if (strlen($this->form->error_message) > 0 || $this->form->fieldsInError()) {
                $this->renderErrorMessage();
            }
            if (strlen($this->form->message) > 0) {
                $this->renderMessage();
            }
            $this->form_started = true;
        }
    }

    public function endForm()
    {
        if ($this->form->show_mandatory_key) {
            $this->renderMandatoryKey();
        }
        $this->closeFormTag();
        $this->form_started = false; //Can be started again now (although I'm not sure why you would want to!)
    }

    public function openFormTag()
    {
        ?>
        <form   method="post"
                action="<?php echo $this->site_url; ?>"
                <?php if ($this->form->form_target && strlen($this->form->form_target) > 0) { ?>target="<?php echo $this->form->form_target; ?>"<?php }
                if (strlen($this->form->id) > 0) { ?> id="<?php echo $this->form->id; ?>"<?php }
                if ($this->form->containsFileUploads()) { ?> enctype="multipart/form-data"<?php } ?>>
        <?php
    }

    public function outputHiddenFields()
    {
        ?>
        <input type="hidden" name="resource" id="resource" value="<?php echo $this->form->form_resource; ?>" />
        <input type="hidden" name="method" id="method" value="<?php echo $this->form->form_method; ?>" />
        <input type="hidden" name="submission_id" id="submission_id" value="<?php echo $this->form->submission_id; ?>" />
        <input type="hidden" name="id" id="record_id" value="<?php echo $this->form->record_id; ?>" />
        <?php
        //Preserve any filters sent in the URL
        if ($this->form->filter_fields) {
            foreach ($this->form->preserved_filters as $key=>$value)
            {
                if (!$this->form->filter_fields->fieldExists('filter_' . $key)) {
                    if (substr($key, 0, 7) == 'filter_') {
                        $key = substr($key, 7);
                    }
                    ?>
                    <input type="hidden" name="<?php echo filter_var('filter_' . $key, FILTER_SANITIZE_FULL_SPECIAL_CHARS); ?>" value="<?php echo filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS); ?>" />
                    <?php
                }
            }
        }
    }

    public function renderErrorMessage()
    {
        ?>
        <div class="error-message <?php echo $this->form->css_class; ?>-error"><?php echo $this->form->error_message; ?></div>
        <?php
    }

    public function renderMessage()
    {
        ?>
        <div class="system-message <?php echo $this->form->css_class; ?>-message" id="system_message"><div id="system_message_close"><a href="javascript:void(0);" title="<?php echo $this->form->getString('close', 'global'); ?>" onclick="document.getElementById('system_message').style.display='none';return false;"><img src="<?php echo $this->form->getString('bare_entry_url', 'routing'); ?>&resource=image&id=close.png" alt="<?php echo $this->form->getString('close', 'global'); ?>" /></a></div><?php echo $this->form->message; ?></div>
        <?php
    }

    public function closeFormTag()
    {
        ?>
        </form>
        <?php
    }

    public function renderFieldSets($field_set_id = null)
    {
        ?>
        <div class="<?php echo $this->form->css_class; ?>-field-sets">
            <?php
            foreach ($this->form->field_sets as $field_set)
            {
                if ($field_set_id == null || $field_set_id == $field_set->id) {
                    $this->renderFieldSet($field_set);
                }
            }
            ?>
        </div>
        <?php
    }

    public function renderFieldSet(FieldSet $field_set)
    {
        if ($field_set->published) {
            ?>
            <<?php echo $field_set->fieldset_tag; if ($field_set->id && strlen($field_set->id) > 0) {echo ' id="' . $field_set->id . '"';} ?><?php if ($field_set->css_class && strlen($field_set->css_class) > 0) { ?> class="<?php echo $field_set->css_class; ?>"<?php } $this->outputAttributes($field_set->attributes); ?>>
                <?php if ($field_set->legend && strlen($field_set->legend) > 0) {
                    $legend_tag = $field_set->fieldset_tag == 'fieldset' ? 'legend' : 'div';
                    ?>
                    <<?php echo $legend_tag ?>><?php echo $field_set->legend; ?></<?php echo $legend_tag; ?>>
                    <?php
                }
                foreach ($field_set->fields as $field)
                {
                    if (isset($field->renderer)) {
                        $field->renderer->render();
                    }
                } ?>
            </<?php echo $field_set->fieldset_tag; ?>>
            <?php
        }
    }

    public function renderMandatoryKey()
    {
        foreach ($this->form->field_sets as $field_set)
        {
            foreach ($field_set->fields as $field)
            {
                if ($field->required) {
                    ?>
                    <div class="<?php echo $this->form->css_class; ?>-mandatory-key">
                        <?php echo $this->form->getString('mandatory', 'form'); ?>
                    </div>
                    <?php
                    break 2;
                }
            }
        }
    }

    protected function outputAttributes($attributes)
    {
        if (count($attributes) > 0) {
            foreach ($attributes as $key=>$value)
            {
                echo ' ' . $key . '="' . $value . '"';
            }
        }
    }
}
