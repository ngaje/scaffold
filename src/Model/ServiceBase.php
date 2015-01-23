<?php
namespace Netshine\Scaffold\Model;

use Netshine\Scaffold\ICms;
use Netshine\Scaffold\Language;
use Netshine\Scaffold\EventData;
use Netshine\Scaffold\Pagination;
use Hra\Hra\Model\Entity\System\Person;

class ServiceBase implements \SplSubject
{
    /** @var DataMaperBase **/
    protected $data_mapper;
    /** @var ICms **/
    protected $cms;
    /** @var Language **/
    protected $language;

    /** @var array **/
    protected $observers = array();

    use \Netshine\Scaffold\TResource;

    public function __construct(ICms $cms, DataMapperBase $data_mapper = null, Language $language)
    {
        $this->cms = $cms;
        $this->data_mapper = $data_mapper;
        $this->language = $language;
    }

    public function attach(\SplObserver $observer) {
        $this->observers[spl_object_hash($observer)] = $observer;
    }

    public function detach(\SplObserver $observer) {

        $key = spl_object_hash($observer);
        if (array_key_exists($key, $this->observers)) {
            unset($this->observers[$key]);
        }
    }

    public function notify(EventData $event = null) {
        foreach ($this->observers as $value) {
            $value->update($this, $event);
        }
    }

    public function setSortOrder($sort_column, $reverse = false)
    {
        if (isset($this->data_mapper)) {
            $this->data_mapper->sort_column = $sort_column;
            $_REQUEST['sort_by'] = $sort_column; //To ensure the correct button is highlighted
            $this->data_mapper->sort_reverse = $reverse;
        }
    }

    public function setPagination(Pagination &$pagination)
    {
        if (isset($this->data_mapper)) {
            $this->data_mapper->pagination =& $pagination;
        }
    }

    /**
    * @param array $filters Associative array of filters
    */
    public function setFilters($filters)
    {
        if (isset($this->data_mapper)) {
            $this->data_mapper->filters = $filters;
        }
    }
}
