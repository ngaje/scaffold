<?php
namespace Ngaje\Scaffold;

trait TResource
{
    /** @var string **/
    protected $resource_name;

    /**
    * Return a string from the language file
    * @param string $key The key of the string to return
    * @param string $resource Optionally specify a resource (language file) - if omitted, the current resource will be guessed
    * @return string
    */
    public function getString($key, $resource = null)
    {
        if ($resource === null) {
            //Guess the resource name based on the class we're in
            $resource = $this->getResourceName();
        }
        $string = $this->language->{$resource}[$key];
        if (strlen($string) == 0) {
            //Try global
            $string = $this->language->global[$key];
        }
        return $string;
    }

    public function getResourceName()
    {
        if (!isset($this->resource_name)) {
            $class_name = implode('', array_slice(explode('\\', get_class($this)), -1));
            $class_words = preg_split('/(?=[A-Z])/', $class_name, -1, PREG_SPLIT_NO_EMPTY);
            array_shift($class_words);
            $this->resource_name = strtolower(implode("_", $class_words));
        }
        return $this->resource_name;
    }
}
