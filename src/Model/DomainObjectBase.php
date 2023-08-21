<?php
namespace Ngaje\Scaffold\Model;

class DomainObjectBase implements \JsonSerializable
{
    public function getProperties($scalar_only = false, $return_data_types = false)
    {
        $reflection = new \ReflectionClass($this);
        return $this->getPropertiesFromAnnotations($reflection, $scalar_only, $return_data_types);
    }

    protected function getPropertiesFromAnnotations(\ReflectionClass $reflection, $scalar_only = false, $return_data_types = false)
    {
        $properties = array();
        $docblock_entries = explode("\n", $reflection->getDocComment());
        foreach ($docblock_entries as $entry)
        {
            $property_start = strpos($entry, '@property');
            if ($property_start !== false) {
                $property_elements = explode(' ', substr($entry, $property_start + 10));
                if (count($property_elements) == 2) {
                    if (!$scalar_only) {
                        $properties[str_replace('$', '', trim($property_elements[1]))] = $property_elements[0];
                    } else {
                        switch ($property_elements[0]) {
                            case 'int':
                            case 'integer':
                            case 'bool':
                            case 'boolean':
                            case 'string':
                            case 'double':
                            case 'float':
                            case 'decimal':
                            case 'array':
                                $properties[str_replace('$', '', trim($property_elements[1]))] = $property_elements[0];
                                break;
                        }
                    }
                }
            }
        }
        $parent = $reflection->getParentClass();
        if ($parent) {
            $properties = array_merge($properties, $this->getPropertiesFromAnnotations($parent, $scalar_only, true));
        }

        return $return_data_types ? $properties : array_keys($properties);
    }

    public function &__get($property)
    {
        //If there is a public getter, use that (won't be passed out by reference though!)
        $method = 'get' . str_replace(" ", "", ucwords(str_replace("_", " ", $property)));
        if(method_exists($this, $method)) {
            $reflection = new \ReflectionMethod($this, $method);
            if ($reflection->isPublic()) {
                $value = $this->{$method}();
                return $value;
            }
        }

        //Otherwise, look for a protected property (can be passed by reference)
        if (property_exists($this, $property)) {
            return $this->{$property};
        }

        throw new \Exception('Property or getter does not exist: ' . get_class($this) . '::' . $property);
    }

    public function __set($property, $value)
    {
        //If there is a public setter, use that
        $method = 'set' . str_replace(" ", "", ucwords(str_replace("_", " ", $property)));
        if(method_exists($this, $method)) {
            $reflection = new \ReflectionMethod($this, $method);
            if ($reflection->isPublic()) {
                return $this->{$method}($value);
            }
        }

        //Otherwise, look for a protected property
        if (property_exists($this, $property)) {
            return $this->$property = $value;
        }

        throw new \Exception('Property or setter does not exist: ' . get_class($this) . '::' . $property);
    }

    public function __isset($property)
    {
        //Look for a protected property
        if (property_exists($this, $property)) {
            return isset($this->{$property});
        } else {
            return false;
        }
    }

    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        //We can bypass getters when serializing (better performance)
        $json = array();
        foreach($this as $key => $value) {
            if ($value instanceof \ArrayAccess) { //Collection
                $this_value = array();
                foreach ($value as $value_key=>$value_item) {
                    if ($value_item instanceof DomainObjectBase) {
                        $this_value[$value_key] = json_decode(json_encode($value_item));
                    } else {
                        $this_value[$value_key] = $value_item;
                    }
                }
                $json[$key] = $this_value;
            } else if ($value instanceof DomainObjectBase) {
                if (property_exists($value, 'id')) {
                    $json[$key] = $value->id;
                }
            } else {
                $json[$key] = $value;
            }
        }
        return $json;
    }
}
