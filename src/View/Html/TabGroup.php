<?php
namespace Ngaje\Scaffold\View\Html;

use Ngaje\Scaffold\ICms;
use Ngaje\Scaffold\Language;

class TabGroup
{
    /** @var ICms **/
    protected $cms;
    /** @var string Unique ID for this set of tabs (in case more than one group are on a page) */
    protected $group_id = "";
    /** @var string ID of the first tab rendered */
    protected $first_tab_id = "";
    /** @var string ID of the tab to be selected automatically */
    public $selected_tab_id = "";
    /** @var Language **/
    public $language;

    public function __construct(ICms $cms, Language $language)
    {
        $this->cms = $cms;
        $this->language = $language;
        $css = htmlentities($this->language->routing['bare_entry_url'] . '&resource=css&id=tab_group');
        $this->cms->addStylesheet($css);
    }

    public function setDefaultSelectedTab($selected_tab_id)
    {
        $this->selected_tab_id = $selected_tab_id;
    }

    /**
    * Begin the output of a tabbed dialog
    * @param string $group_id Unique ID for this set of tabs (in case more than one group are on a page)
    * @param boolean Whether or not to collapse the tabs for narrow displays (less than 500px wide, but that could be changed by altering the tab_group.css stylesheet)
    */
    public function startTabGroup($group_id, $responsive = false)
    {
        $this->group_id = $group_id;
        ob_start();
        ?>
        <script type="text/javascript">

        function selectTab_<?php echo $group_id; ?>(tab_id, force_select)
        {
            if (!document.getElementById(tab_id)) {
                //If selected using shortened form, pad it out to the full ID
                tab_id = 'tab-title-<?php echo $group_id; ?>-' + tab_id;
                if (!document.getElementById(tab_id)) {
                    return false;
                }
            }
            if (force_select || !window.disable_tabs) {
                if (tab_id && tab_id.length > 0)
                {
                    var page_id = tab_id.split('-').pop();

                    var divs = document.getElementsByTagName('div');
                    for(var i=0; i<divs.length; i++)
                    {
                        if (divs[i].id.indexOf('tab-title-<?php echo $group_id; ?>') > -1 && divs[i].id != tab_id)
                        {
                            this_tab_id = divs[i].id.split('-').pop();
                            divs[i].className = divs[i].className.replace(' selected', '');
                            if (document.getElementById('tab-content-<?php echo $group_id; ?>-' + this_tab_id)) {
                                document.getElementById('tab-content-<?php echo $group_id; ?>-' + this_tab_id).style.display = 'none';
                            }
                        }
                    }
                    if (document.getElementById('tab-content-<?php echo $group_id; ?>-' + page_id)) {
                        document.getElementById('tab-content-<?php echo $group_id; ?>-' + page_id).style.display = '';
                    }
                    if (document.getElementById(tab_id).className.indexOf(' selected') == -1) {
                        document.getElementById(tab_id).className += ' selected';
                    }
                    document.getElementById('selected_tab_<?php echo $group_id; ?>').value = tab_id;
                }

                if (window.onresize)
                {
                    setTimeout(function(){window.onresize();}, 200); //In case the change in content affects dynamically positioned elements (eg. footer)
                }
            }
        }

        </script>
        <?php $js_function = ob_get_clean();
        $this->cms->addHeadContent($js_function);
        ?>
        <div id="tab-group-<?php echo $group_id; ?>" class="tab-group<?php if ($responsive) {echo ' responsive-tabs';} ?>">
        <?php
    }

    /**
    * Add a new tab to the group
    * @param string $page_id Unique ID for this tab within the group
    * @param string $caption Caption to display on the tab
    * @param string $onclick_before Javascript to execute on click event before the tab is selected (NOTE: Always terminate this value with a semi-colon!)
    * @param string $onclick_after Javascript to exectue on click event after the tab is selected (NOTE: Always terminate this value with a semi-colon!)
    */
    public function add_tab_title($page_id, $caption, $onclick_before="", $onclick_after="", $css_class="", $attributes="")
    {
        if ($onclick_before) {
            $onclick_before = substr($onclick_before, strlen($onclick_before) - 1) == ";" ? $onclick_before : $onclick_before . ";";
        }
        ?>
        <div id="tab-title-<?php echo $this->group_id; ?>-<?php echo $page_id; ?>" class="tab-title <?php echo $css_class; ?>" onclick="<?php echo $onclick_before; ?>selectTab_<?php echo $this->group_id; ?>(this.id);<?php echo $onclick_after; ?>" <?php echo $attributes; ?>>
            <?php echo $caption; ?>
        </div>

        <?php
        if (strlen($this->selected_tab_id) == 0)
        {
            $this->selected_tab_id = 'tab-title-' . $this->group_id . '-' . $page_id;
        }
        if (strlen($this->first_tab_id) == 0)
        {
            $this->first_tab_id = 'tab-title-' . $this->group_id . '-' . $page_id;
        }
    }

    /**
    * Add the content for a given tab
    * @param string $page_id The ID of the tab, as specified when previously calling @see add_tab_title
    * @param string $content The content to display when this tab is selected
    */
    public function add_tab_content($page_id, $content)
    {
        ?>
        <div id="tab-content-<?php echo $this->group_id; ?>-<?php echo $page_id; ?>" class="tab-content">
            <?php echo $content; ?>
        </div>
        <?php
    }

    /**
    * Finish off the tab page and select the first tab
    */
    public function end_tab_group()
    {
        ?>
        <input type="hidden" name="selected_tab_<?php echo $this->group_id; ?>" id="selected_tab_<?php echo $this->group_id; ?>" value="<?php echo $this->selected_tab_id; ?>" />
        </div>
        <script type="text/javascript">
            if (window.disable_tabs) {
                selectTab_<?php echo $this->group_id; ?>('<?php echo $this->first_tab_id; ?>', true);
            } else {
                selectTab_<?php echo $this->group_id; ?>('<?php echo $this->selected_tab_id; ?>');
            }
        </script>
        <?php
    }
}
