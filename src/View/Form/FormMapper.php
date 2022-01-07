<?php
namespace Ngaje\Scaffold\View\Form;

use Ngaje\Scaffold\Request;
use Ngaje\Scaffold\Model\DomainObjectBase;
use Ngaje\Scaffold\View\Form\FieldTypes\FieldLabel;
use Ngaje\Scaffold\View\Form\FieldTypes\FieldLinkButton;

class FormMapper
{
    const DIRECTION_ENTITY_TO_FORM = 0;
    const DIRECTION_FORM_TO_ENTITY = 1;

    /** @var Request **/
    protected $request;
    /** @var FormBase **/
    protected $form;

    public function __construct(Request $request, FormBase $form)
    {
        $this->request = $request;
        $this->form = $form;
    }

    public function mapFormFromRequest($raw_values = array())
    {
        if (!$this->form->initialised) {
            $this->form->initialise();
        }
        foreach ($_REQUEST as $key=>$value)
        {
            if ($key == 'id') {
                $this->form->record_id = substr($value, 0, 3) == 'new' ? $value : intval($value);
            }
            if ($this->form->fieldExists($key, true)) {
                $field = $this->form->getField($key, true);
                if (array_search($key, $raw_values) !== false) {
                    $field->setValueRaw($this->request->getRequestParam($key, null, null));
                } else {
                    $field->setValue($this->request->getRequestParam($key));
                }
                if (array_key_exists('confirm_' . $key, $_REQUEST)) {
                    $field->setConfirmValue($this->request->getRequestParam('confirm_' . $key));
                }
            }
        }

        //If any values were not sent back, mark them as not published
        foreach ($this->form->field_sets as &$field_set)
        {
            foreach ($field_set->fields as &$field)
            {
                switch ($field->type)
                {
                    case 'button':
                    case 'submit':
                    case 'reset':
                    case 'container':
                        break;//Update PHP 7.3
                    default:
                        if (!($field instanceof FieldLinkButton)) {
                            if (!array_key_exists($field->name, $_REQUEST) && !array_key_exists($field->name, $_FILES)) {
                                $field->published = false;
                            }
                        }
                        break;
                }
            }
        }

        return $this->form;
    }

    /**
    * @param DomainObjectBase $domain_object
    * @param boolean $labels_only
    * @return FormBase
    */
    public function mapFormFromEntity(DomainObjectBase $domain_object, $labels_only = false)
    {
        if (!$this->form->initialised) {
            $this->form->initialise();
        }
        if (isset($domain_object->id)) {
            $this->form->record_id = $domain_object->id;
        }
        return $this->mapEntityFields($domain_object, self::DIRECTION_ENTITY_TO_FORM, $labels_only);
    }

    /**
    * @param DomainObjectBase $domain_object
    * @return DomainObjectBase
    */
    public function mapEntityFromForm(DomainObjectBase $domain_object)
    {
        if (!$this->form->initialised) {
            $this->form->initialise();
        }
        return $this->mapEntityFields($domain_object, self::DIRECTION_FORM_TO_ENTITY);
    }

    /**
    * @param DomainObjectBase $domain_object
    * @param int $direction
    * @return DomainObjectBase|FormBase
    */
    protected function mapEntityFields(DomainObjectBase $domain_object, $direction = self::DIRECTION_ENTITY_TO_FORM, $labels_only = false)
    {
        $properties = $domain_object->getProperties(false, true);
        foreach ($properties as $property=>$data_type)
        {
            if ($domain_object->$property instanceof DomainObjectBase) {
                $child_object = $domain_object->$property;
                $child_properties = $child_object->getProperties(true);
                $prefix = $property . '_';
                $this->applyPropertyMappings($child_object, $child_properties, $direction, $prefix, $labels_only);
            } else if ($data_type == 'DateTime' || $data_type == '\DateTime') {
                if ($direction == self::DIRECTION_ENTITY_TO_FORM) {
                    $date_value = $domain_object->$property;
                    if ($date_value && $date_value instanceof \DateTime) {
                        $this->mapEntityValueToField($property, $domain_object->$property);
                    }
                } else {
                    $date_field = $this->form->getField($property, true);
                    if ($date_field) {
                        $date_value = $date_field->getValue();
                        if ($date_value) {
                            $date_value = new \DateTime($date_value);
                        }
                        $this->mapFieldValueToEntity($property, $date_value ? $date_value : null, $domain_object);
                    }
                }
            }
        }
        return $this->applyPropertyMappings($domain_object, $domain_object->getProperties(true), $direction, '', $labels_only);
    }

    /**
    * @param DomainObjectBase $domain_object
    * @param array $properties
    * @param int $direction
    * @return DomainObjectBase|FormBase
    */
    protected function applyPropertyMappings(DomainObjectBase $domain_object, $properties, $direction, $prefix = '', $labels_only = false)
    {
        foreach ($properties as $property)
        {
            $field = null;
            $field_name = null;
            $entity_value = null;
            $form_value = null;

            if ($this->form->fieldExists($prefix . $property)) {
                $field_name = $prefix . $property;
            }
            if ($field_name !== null) {
                $entity_value = $domain_object->$property;
                $field = $this->form->getField($field_name);
                if ($labels_only && !($field instanceof FieldLabel)) {
                    $field = null;
                } else {
                    $form_value = $field->value;
                }
            }

            if ($field != null) {
                if ($direction == self::DIRECTION_ENTITY_TO_FORM) {
                    if ($entity_value !== null) {
                        $this->mapEntityValueToField($field_name, $this->convertCountries($field, $entity_value, true));
                    }
                } else {
                    if ($form_value !== null && !$this->form->getField($field_name)->is_read_only) {
                        $this->mapFieldValueToEntity($property, $this->convertCountries($field, $form_value, false), $domain_object);
                    }
                }
            }
        }
        if ($direction == self::DIRECTION_ENTITY_TO_FORM) {
            return $this->form;
        } else {
            return $domain_object;
        }
    }

    protected function mapFieldValueToEntity($property, $field_value, &$entity)
    {
        if (isset($entity) && $entity instanceof DomainObjectBase) {
            $entity->$property = $field_value;
        }
    }

    protected function mapEntityValueToField($field_name, $entity_value)
    {
        $field = $this->form->getField($field_name);
        if ($field !== null) {
            switch ($field->type) {
                case 'label':
                    $field->setValueRaw($entity_value);
                    break;
                case 'date':
                    if ($entity_value instanceof \DateTime) {
                        $field->setValue($entity_value->format('Y-m-d'));
                    } else {
                        $field->setValue($entity_value);
                    }
                    break;
                case 'datetime-local':
                    if ($entity_value instanceof \DateTime) {
                        $field->setValue($entity_value->format('Y-m-d\TH:i'));
                    } else {
                        $field->setValue($entity_value);
                    }
                    break;
                default:
                    $field->setValue($entity_value);
                    break;
            }
        }
    }

    protected function convertCountries($field, $value, $add_prefix = false)
    {
        $return_value = $value;

        //Country codes are a special case as they need a prefix to avoid clashing with ini file format for language handling
        if (count($field->options) > 1 && substr(key($field->options), 0, 3) == 'CC_') {
            if ($add_prefix) {
                if (strlen($value) == 2) {
                    $return_value = 'CC_' . $value;
                }
            } else if (strlen($value) == 5 && substr($value, 0, 3) == 'CC_') {
                $return_value = substr($value, 3);
            }
        }

        return $return_value;
    }
}
