<?php
namespace Ngaje\Scaffold\View\Form\FieldTypes;

use Ngaje\Scaffold\View\Form\FieldRenderer;

class FieldRendererSlideSwitch extends FieldRenderer
{
    public function render()
    {
        if ($this->field->published) {
            $this->loadJsFiles();
            $this->preRender();
            $this->renderErrorMessage();
            $this->renderPreControl();
            $this->renderControl();
            $this->renderPostControl();
            $this->renderCaption();
            if ($this->field->request_confirmation) {
                $this->renderPreControl(true);
                $this->renderControl(null, true);
                $this->renderPostControl(true);
                $this->renderCaption(true);
            }
            $this->renderHelpText();
            $this->postRender();
        }
    }

    protected function renderControl($type = null, $confirmation = false)
    {
        ?>
            <label class="slider-label"><?php echo $this->field->attributes['label']; ?></label>	        
		<label class="field-control fld-slide-switch <?php echo $this->field->css_class; ?>">
		        <input type="checkbox" name="<?php echo $this->field->name; ?>" <?php $this->outputId(null, '0'); ?> value="<?php echo $this->field->getValue($confirmation)  ;?>" <?php if ($this->field->value) {echo ' checked="checked"';} ?> <?php $this->outputAttributes($this->field->attributes); ?>>
		        <span class="switch-slider" title="<?php echo $this->field->attributes['title']; ?>"></span>
	        </label>
	    <?php
    }
}
