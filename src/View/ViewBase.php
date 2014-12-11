<?php
namespace Netshine\Scaffold\View;

use Netshine\Scaffold;
use Netshine\Scaffold\Url;
use Netshine\Scaffold\ICms;
use Netshine\Scaffold\Language;
use Netshine\Scaffold\View\Form\FormBase;
use Netshine\Scaffold\View\Form\FormRenderer;
use Netshine\Scaffold\View\Form\FieldBase;
use Netshine\Scaffold\View\Form\FieldFactory;
use Netshine\Scaffold\View\Form\FieldSet;

abstract class ViewBase
{
    /** @var ICms **/
    protected $cms;
    /** @var Language **/
    protected $language;
    /** @var FormBase **/
    protected $form;
    /** @var string **/
    protected $title = '';
    /** @var string **/
    protected $intro = '';
    /** @var string **/
    protected $footer = '';
    /** @var FieldFactory **/
    protected $field_factory;
    /** @var array **/
    public $filters = array();

    /** @var boolean**/
    protected $sort_button_js_loaded = false;

    use Scaffold\TResource;

    public function __construct(ICms $cms, Language $language, FormBase $form = null, FormRenderer $form_renderer = null, FieldFactory $field_factory = null)
    {
        $this->cms = $cms;
        $this->language = $language;
        $this->field_factory = $field_factory;
        if ($form) {
            $this->form = $form;
            $this->form->setRenderer($form_renderer);
            if ($this->field_factory) {
                $this->form->filter_fields = $this->field_factory->createFieldSet($this->form, '', 'filters');
                $this->form->filter_fields->fieldset_tag = 'div';
            }
        }
        $this->initialise();
    }

    /**
    * Can be overridden if you want to do some work without changing the constructor
    */
    public function initialise()
    {
        //Include JQuery early, so that feature-specific javascript files can use it straight away
        $this->cms->addJavascript($this->language->routing['bare_entry_url'] . '&resource=js&id=jquery/jquery-1.11.1.min.js');
    }

    public function renderSortButtons($col_name, Url $url = null)
    {
        if (!$url) {
            $url = new Url();
            $url->removeQuerystringParams(array('sort_by', 'sort_reverse', 'message'));
        }
        $this->loadSortButtonJs($url);
        $asc_selected = isset($_REQUEST['sort_by']) && $_REQUEST['sort_by'] == $col_name && !@$_REQUEST['sort_reverse'];
        $desc_selected = isset($_REQUEST['sort_by']) && $_REQUEST['sort_by'] == $col_name && @$_REQUEST['sort_reverse'];

        if (!$asc_selected) {
            ?>
            <a href="<?php echo $url; ?>&sort_by=<?php echo $col_name; ?>" onclick="sort_submit('<?php echo $col_name; ?>');return false;" style="text-decoration:none;">
            <?php
        } ?>
        <img src="<?php echo $this->language->routing['bare_entry_url']; ?>&resource=image&id=ascending<?php echo $asc_selected ? '_selected' : ''; ?>" alt="<?php echo $this->language->scaffold['ascending']; ?>" />
        <?php if (!$asc_selected) { ?>
            </a>
        <?php }

        if (!$desc_selected) {
            ?>
            <a href="<?php echo $url; ?>&sort_by=<?php echo $col_name; ?>&sort_reverse=1" onclick="sort_submit('<?php echo $col_name; ?>', true);return false;" style="text-decoration:none;">
            <?php
        } ?>
        <img src="<?php echo $this->language->routing['bare_entry_url']; ?>&resource=image&id=descending<?php echo $desc_selected ? '_selected' : ''; ?>" alt="<?php echo $this->language->scaffold['ascending']; ?>" />
        <?php if (!$desc_selected) { ?>
            </a>
            <?php
        }
    }

    protected function loadSortButtonJs(Url $url)
    {
        if (!$this->sort_button_js_loaded) {
            ob_start();
            ?>
            <script type="text/javascript">
            function sort_submit(column, reverse)
            {
                var form = document.getElementById('<?php echo $this->form->id; ?>');
                if (!form) {
                    form = document.getElementsByTagName('form')[0];
                }
                if (form) {
                    form.action = '<?php echo $url; ?>&sort_by=' + column + (reverse ? '&sort_reverse=1' : '');
                    form.submit();
                }
            }
            </script>
            <?php
            $js = ob_get_clean();
            $this->cms->addHeadContent($js);
            $this->sort_button_js_loaded = true;
        }
    }

    public function renderFilterFields($show_caption = true)
    {
        if ($show_caption && count(@$this->form->filter_fields->fields) > 0) {
            echo $this->getString('filters', 'global');
        }
        foreach ($this->form->filter_fields->fields as $filter_field)
        {
            ?>
            <span class="filter-group"><?php $filter_field->render(); ?></span>
            <?php
        }
    }

    public function renderFilterButtons()
    {
        if (count(@$this->form->filter_fields->fields) > 0) {
            ?>
            <span class="filter-group">
                <input type="submit" name="filter_go" id="filter_go" value="<?php echo $this->language->scaffold['go']; ?>" class="field-control fld-submit filter-button" />
                <input type="submit" name="filter_reset" id="filter_reset" value="<?php echo $this->language->scaffold['reset']; ?>" class="field-control fld-submit filter-button" onclick="var elems=document.body.getElementsByTagName('*');for(var i=0;i<elems.length;i++){if(elems[i].id&&elems[i].id.indexOf&&elems[i].id.indexOf('filter_')===0&&elems[i].id!='filter_go'&&elems[i].id!='filter_reset'){elems[i].value='';if(elems[i].selectedIndex){elems[i].selectedIndex=-1;}}}" />
            </span>
            <?php
        }
    }

    protected function addFiltersQueryString($current_string = '')
    {
        $query_string = '';
        foreach ($this->form->filter_fields->fields as $filter_field)
        {
            if (strlen($filter_field->getValue()) > 0) {
                $this_query = '&' . $filter_field->name . '=' . urlencode($filter_field->getValue());
                if (strpos($current_string, $this_query) === false) {
                    $query_string .= $this_query;
                }
            }
        }
        return $query_string;
    }
}