<?php
namespace Netshine\Scaffold\View\Form;

use Netshine\Scaffold\Language;
use Netshine\Scaffold\ICms;

class FieldRenderer
{
    /** @var FieldBase **/
    protected $field;
    /** @var Language **/
    protected $language;
    /** @var ICms **/
    protected $cms;

    static $js_loaded = false;

    public function __construct(FieldBase $field, Language $language, ICms $cms)
    {
        $this->field = $field;
        $this->field->renderer = $this;
        $this->language = $language;
        $this->cms = $cms;
    }

    public function render()
    {
        if ($this->field->published) {
            $this->loadJsFiles();
            $this->preRender();
            $this->renderErrorMessage();
            $this->renderCaption();
            $this->renderPreControl();
            $this->renderControl();
            $this->renderPostControl();
            if ($this->field->request_confirmation) {
                $this->renderCaption(true);
                $this->renderPreControl(true);
                $this->renderControl(null, true);
                $this->renderPostControl(true);
            }
            $this->renderHelpText();
            $this->postRender();
        }
    }

    protected function loadJsFiles()
    {
        if (!self::$js_loaded) {
            $this->cms->addJavascript($this->language->routing['bare_entry_url'] . '&resource=js&id=modernizr/modernizr.custom.js');
            $this->cms->addJavascript($this->language->routing['bare_entry_url'] . '&resource=js&id=webshim/polyfiller.js');
            //$this->cms->addJavascript('//cdn.jsdelivr.net/webshim/1.14.5/polyfiller.js');

            ?>
            <script type="text/javascript">
                webshim.setOptions('basePath', '<?php echo $this->language->routing['bare_entry_url'] . '&resource=js&id=webshim/shims/'; ?>')
                webshim.activeLang('<?php echo $this->language->getCurrentLanguage(); ?>');
                //Configure forms features
                webshim.setOptions("forms", {
                    lazyCustomMessages: true,
                    replaceValidationUI: true,
                    customDatalist: "auto",
                    list: {
                        "filter": "^"
                    }
                });

                //Configure forms-ext features
                webshim.setOptions("forms-ext", {
                    replaceUI: "auto",
                    types: "date range number",
                    date: {
                        startView: 2,
                        openOnFocus: true,
                        classes: "show-week",
                        dateSigns: '-',
                        dateFormat: 'yy-mm-dd'
                    },
                    number: {
                        calculateWidth: false
                    },
                    range: {
                        classes: "show-activevaluetooltip"
                    },
                    datepicker: {
                        dateFormat: 'yy-mm-dd'
                    }
                });

                //Load forms and forms-ext features
                webshim.polyfill('forms forms-ext');
            </script>
            <?php
            self::$js_loaded = true;
        }
    }

    protected function preRender()
    {
        ?>
        <div class="field type_<?php echo $this->field->type; ?> <?php echo $this->field->css_class; ?>"<?php $this->outputId('fld'); if (!$this->field->visible) {echo ' style=display:none;';} $this->outputAttributes($this->field->container_attributes); ?>>
        <?php
    }

    protected function renderErrorMessage()
    {
        if (strlen($this->field->error) > 0) {
            ?>
            <div class="field-error fld-<?php echo $this->field->type; ?> <?php echo $this->field->css_class; ?>"><?php echo $this->field->error; ?></div>
            <?php
        }
    }

    protected function renderCaption($confirmation = false)
    {
        $caption = $confirmation ? sprintf($this->language->form['fld_confirm_caption'], $this->field->caption) : $this->field->caption;

        if ($this->field->caption) {
            ?><label class="field-caption fld-<?php echo $this->field->type; ?> <?php echo $this->field->css_class; ?>"<?php $this->outputId(($confirmation ? 'confirm_' : '') . 'caption'); if($this->field->id) {?> for="<?php echo ($confirmation ? 'confirm_' : '') . $this->field->id; ?>"<?php } ?>><?php
            if ($this->field->parent_field_set->parent_form->show_mandatory_key && $this->field->required) {
                echo '* ';
            }
            echo $caption;
            ?></label><?php
        }
    }

    protected function renderHelpText()
    {
        if ($this->field->help_text) {
            $elem_id = 'help_' . $this->field->id;
            ?>
            <a title="<?php echo $this->language->global['help']; ?>" href="javascript:void(0);" onclick="$('#<?php echo $elem_id; ?>').slideToggle(400);this.blur();return false;"><?php
                ?><img src="<?php echo $this->language->routing['bare_entry_url']; ?>&resource=image&id=help.png" border="0" alt="<?php echo $this->language->global['help']; ?>" /><?php
            ?></a>

            <div class="help-text field-help-text fld-<?php echo $this->field->type; ?> <?php echo $this->field->css_class; ?>"<?php $this->outputId('help'); ?> style="display:none;"><?php
            echo $this->field->help_text;
            ?></div><?php
        }
    }

    protected function renderPreControl($confirmation = false)
    {
        echo $this->field->pre_control;
    }

    protected function renderControl($type = null, $confirmation = false)
    {
        if (!$type) {
            $type = $this->field->type;
        }
        ?>
        <input type="<?php echo $type; ?>" name="<?php echo ($confirmation ? 'confirm_' : '') . $this->field->name; ?>" class="field-control fld-<?php echo $this->field->type; ?> <?php echo $this->field->css_class; ?>" <?php $this->outputId($confirmation ? 'confirm_' : ''); $this->outputAttributes($this->field->attributes); if ($this->field->getValue($confirmation)) {echo ' value="' . $this->field->getValue($confirmation) . '"';} if ($this->field->required) {echo ' required="required"';} ?> />
        <?php
    }

    protected function renderPostControl($confirmation = false)
    {
        echo $this->field->post_control;
    }

    protected function postRender()
    {
        ?>
        </div>
        <?php
    }

    protected function outputId($prefix = '', $suffix = '')
    {
        if (strlen($this->field->id) > 0) {
            echo ' id="';
            echo strlen($prefix) > 0 ? $prefix . '_' : '';
            echo $this->field->id;
            echo strlen($suffix) > 0 ? '_' . $suffix : '';
            echo '"';
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