<?php
namespace Ngaje\Scaffold;

use Ngaje\Scaffold\ICms;
use Ngaje\Scaffold\Url;
use Ngaje\Scaffold\Language;

/**
* @property int $page_no
* @property int $records_per_page
* @property int $total_records
* @property int $max_links
*/
class Pagination
{
    /** @var Url **/
    public $url;
    /** @var int **/
    protected $page_no = 1;
    /** @var int **/
    protected $records_per_page;
    /** @var int **/
    protected $total_records;
    /** @var int **/
    protected $max_links;

    /** @var ICms **/
    protected $cms;
    /** @var int **/
    protected $no_of_pages;
    /** @var int **/
    protected $first_page_link;
    /** @var int **/
    protected $last_page_link;

    /** @var Language **/
    protected $language;

    public function __construct(ICms $cms, $records_per_page, $total_records, $page_no = 1, $max_links = 15, Url $url = null)
    {
        $this->cms = $cms;
        $this->setBaseUrl($url);
        $this->page_no = $page_no == 0 ? 1 : $page_no;
        $this->records_per_page = $records_per_page;
        if ($this->records_per_page < 1) {
            $this->records_per_page = 1; //Avoid division by zero!
        }
        $this->total_records = $total_records;
        $this->max_links = $max_links;
        $this->calculatePages();
    }

    protected function setBaseUrl(Url $url = null)
    {
        if ($url === null) {
            $url = new Url(); //Use currently requested page
        }
        //Remove page number if present, so we don't end up with a long chain of concatenated page numbers in the address bar
        $url->removeQuerystringParams(array('page', 'records_per_page', 'message'));
        $this->url = $url;
    }

    public function __set($property, $value)
    {
        switch ($property)
        {
            case 'page_no':
            case 'records_per_page':
            case 'total_records':
            case 'max_links':
                $this->$property = intval($value);
                $this->calculatePages();
                break;
        }
    }

    public function __get($property)
    {
        switch ($property)
        {
            case 'page_no':
            case 'records_per_page':
                return intval($this->$property) > 0 ? intval($this->$property) : 1; //No division by zero
            case 'total_records':
            case 'max_links':
                return intval($this->$property);
        }
    }

    public function calculatePages()
    {
        $this->no_of_pages = ceil($this->total_records / $this->records_per_page);

        $this->first_page_link = 2;
        $this->last_page_link = $this->no_of_pages - 1;

        if ($this->no_of_pages > $this->max_links) {
            if ($this->page_no > $this->max_links / 2) {
                //Offset needed at start
                $spare = (floor($this->max_links / 2) - ($this->no_of_pages - $this->page_no)) + 1;
                $spare = $spare > 0 ? $spare : 0;
                $this->first_page_link = ($this->page_no - (floor($this->max_links / 2)- 1)) - $spare;
            }
            $this->last_page_link = ($this->first_page_link + $this->max_links) - 1;
        }
    }

    public function renderControls(Language $language, $form_id = null)
    {
        $this->language = $language;
        $this->createSubmitJs($form_id);

        $this->renderRecordCount();
        ?>
        <span class="pagination-controls">
            <?php
            $this->renderFirstPageLink();
            $this->renderPageLinks();
            $this->renderLastPageLink();
            ?>
        </span>
        <?php
        $this->renderRecordsPerPage();
    }

    protected function createSubmitJs($form_id)
    {
        if ($form_id) {
            $form_elem = 'document.getElementById(\'' . $form_id . '\')';
        } else {
            $form_elem = 'document.getElementsByTagName(\'form\')[0]';
        }
        ob_start();
        ?>
        <script type="text/javascript">
        function pagination_submit(page_no)
        {
            <?php echo $form_elem; ?>.action='<?php echo $this->url; ?>&page=' + page_no;
            <?php echo $form_elem; ?>.submit();
        }
        </script>
        <?php
        $js = ob_get_clean();
        $this->cms->addHeadContent($js);
    }

    protected function renderRecordCount()
    {
        $from = (($this->page_no * ($this->records_per_page)) - $this->records_per_page) + 1;
        $from = $from < 1 || $this->total_records == 0 ? 0 : $from;
        $to = $from + ($this->records_per_page - 1);
        $to = $to > $this->total_records ? $this->total_records : $to;
        ?>
        <div class="pagination-record-count">
            <?php echo sprintf($this->language->scaffold['pagination_record_count'], $from, $to, $this->total_records); ?>
        </div>
        <?php
    }

    protected function renderFirstPageLink()
    {
        if ($this->page_no <= 1) { ?>
            <span class="pagination-first disabled">&lt;&lt; <?php echo $this->language->scaffold['first']; ?></span> |
        <?php } else { ?>
            <a href="<?php echo $this->url; ?>&page=1&records_per_page=<?php echo $this->records_per_page; ?>" onclick="pagination_submit(1);return false;">&lt;&lt; <?php echo $this->language->scaffold['first']; ?></a> |
        <?php
        }
        if ($this->first_page_link > 2) {
            //Some early pages are skipped
            ?> ... <?php
        }
    }

    protected function renderPageLinks()
    {
        for ($page_no = $this->first_page_link; $page_no <= $this->last_page_link && $page_no < $this->no_of_pages; $page_no++) {
            if ($page_no == $this->page_no) {
                echo $page_no . ' | ';
            } else {
                ?>
                <a href="<?php echo $this->url; ?>&page=<?php echo $page_no; ?>&records_per_page=<?php echo $this->records_per_page; ?>" onclick="pagination_submit(<?php echo $page_no; ?>);return false;"><?php echo $page_no; ?></a> |
                <?php
            }
        }
    }

    protected function renderLastPageLink()
    {
        if ($this->last_page_link < $this->no_of_pages - 1) {
            //Some later pages are skipped
            ?> ... <?php
        }

        if ($this->page_no >= $this->no_of_pages) {
            ?><span class="pagination-last disabled"><?php echo $this->language->scaffold['last']; ?> &gt;&gt;</span><?php
        } else {
            ?>
            <a href="<?php echo $this->url; ?>&page=<?php echo $this->no_of_pages; ?>&records_per_page=<?php echo $this->records_per_page; ?>" onclick="pagination_submit(<?php echo $this->no_of_pages; ?>);return false;"><?php echo $this->language->scaffold['last']; ?> &gt;&gt;</a>
            <?php
        }
    }

    protected function renderRecordsPerPage()
    {
        ?>
        <div class="pagination-records-per-page">
            <label id="records_per_page_label" for="records_per_page"><?php echo $this->language->scaffold['records_per_page']; ?></label>
            <select id="records_per_page" name="records_per_page" onchange="pagination_submit(<?php echo $this->page_no; ?>);" style="width:auto;">
                <option<?php if ($this->records_per_page == 10) {echo ' selected="selected"';} ?>>10</option>
                <option<?php if ($this->records_per_page == 25) {echo ' selected="selected"';} ?>>25</option>
                <option<?php if ($this->records_per_page == 50) {echo ' selected="selected"';} ?>>50</option>
                <option<?php if ($this->records_per_page == 100) {echo ' selected="selected"';} ?>>100</option>
                <option<?php if ($this->records_per_page == 150) {echo ' selected="selected"';} ?>>150</option>
                <option<?php if ($this->records_per_page == 200) {echo ' selected="selected"';} ?>>200</option>
            </select>
        </div>
        <?php
    }
}
