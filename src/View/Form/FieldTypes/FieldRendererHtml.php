<?php
namespace Netshine\Scaffold\View\Form\FieldTypes;

use Netshine\Scaffold\View\Form\FieldRenderer;

class FieldRendererHtml extends FieldRenderer
{
    protected function loadJsFiles()
    {
        parent::loadJsFiles();
        $this->cms->addJavascript($this->language->routing['bare_entry_url'] . '&resource=js&id=nicedit/nicEdit');
        $this->cms->addStylesheet($this->language->routing['bare_entry_url'] . '&resource=css&id=nicedit/nicedit');
    }

    public function renderControl($type = null, $confirmation = false)
    {
        ?>
        <div class="nicEdit-container">
            <textarea name="<?php echo $this->field->name; ?>" <?php $this->outputId(); $this->outputAttributes($this->field->attributes); ?> class="field-control fld-html <?php echo $this->field->css_class; ?>"><?php echo $this->field->value; ?></textarea>
            <script type="text/javascript">
                var <?php echo $this->field->name; ?> = null;
                setTimeout(function(){try{<?php echo $this->field->name; ?>=new nicEditor({iconsPath:'<?php echo $this->language->routing['bare_entry_url']; ?>&resource=image&id=nicedit/nicEditorIcons.gif', fullPanel:true}).panelInstance('<?php echo $this->field->name; ?>');}catch(err){alert(err);}},750);
            </script>
        </div>
        <?php
    }
}