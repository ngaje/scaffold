<?php
namespace Ngaje\Scaffold\Model;

use Ngaje\Scaffold\Pagination;
use Ngaje\Scaffold\Database;
use Ngaje\Scaffold\Language;
use Ngaje\Scaffold\Model\DomainObjectBase;
use Ngaje\Scaffold\View\Form\FormBase;

class DataMapperBase
{
    /** @var Database **/
    protected $db;

    /** @var string **/
    public $sort_column;
    /** @var boolean **/
    public $sort_reverse = false;
    /** @var Pagination **/
    public $pagination;
    /** @var Language **/
    public $language;
    /** @var array **/
    public $filters = array();
    /** @var boolean **/
    public $paginate = true;
  	/** @var boolean **/
	protected $fetch_join_collection = false;//Doctrine Paginator setting. True causes strict issues with Doctrine Paginator in MySQL 5.7 +

    public function __construct(Database $db, Pagination $pagination, Language $language)
    {
        $this->db = $db;
        $this->pagination = $pagination;
        $this->language = $language;
    }

    public function __destruct()
    {
        if (isset($this->db)) {
            @$this->db->close();
            $this->db = null;
        }
    }

    /**
    * Maps data from the form to the entity where the field name matches the name of an entity's property.
    * Optionally specify a prefix if the form fields have one.
    * @param FormBase $form Form containing the values to map
    * @param DomainObjectBase $entity Entity to map the values to
    * @param string $name_prefix Field name prefix that may be applied to form field names
    * @param boolean $strict Where a name prefix is supplied, set this to true if you ONLY want to map fields that have a matching prefix (otherwise it will map fields that have the prefix or no prefix)
    * @return DomainObjectBase
    */
    protected function mapScalarFormElements(FormBase $form, DomainObjectBase $entity, $name_prefix = '', $strict = false)
    {
        $properties = $entity->getProperties(true);
        foreach ($properties as $property)
        {
            if ($form->fieldExists($name_prefix . $property)) {
                $entity->$property = $form->getField($name_prefix . $property)->value;
            } else if (strlen($name_prefix) > 0 && !$strict) {
                if ($form->fieldExists($property)) {
                    $entity->$property = $form->getField($property)->value;
                }
            }
        }
        return $entity;
    }

    /**
    * Persist the given entity, optionally flushing to the database
    * @param mixed $entity Entity to persist
    * @param mixed $flush Whether or not to write to the database immediately (if persisting several entities, wait until the last one before flushing)
    */
    public function saveEntity(DomainObjectBase $entity, $flush = true)
    {
        $this->db->getDoctrine()->persist($entity);
        if ($flush) {
            $this->flush();
        }
    }

    public function deleteEntity(DomainObjectBase $entity, $flush = true)
    {
        $this->db->getDoctrine()->remove($entity);
        if ($flush) {
            $this->flush();
        }
    }

    public function detachEntity(DomainObjectBase $entity)
    {
        $this->db->getDoctrine()->detach($entity);
    }

    public function mergeEntity(DomainObjectBase $entity)
    {
        $this->db->getDoctrine()->merge($entity);
    }

    public function flush()
    {
        $this->db->getDoctrine()->flush();
    }

    public function abort()
    {
        $this->db->getDoctrine()->clear();
    }
}
