<?php
namespace Ngaje\Scaffold\Tools;

use Ngaje\Scaffold\Database;
use Doctrine\ORM;
use Doctrine\ORM\Tools\SchemaValidator;

class DatabaseBuilder
{
    /** @var Database */
    public $db;

    protected $class_schemas = array();
    protected $excluded_class_names = array();

    public function __construct(Database $db, $exclude_class_names = array(), $class_schemas = array())
    {
        $this->db = $db;
        $this->excluded_class_names = $exclude_class_names;
        $this->class_schemas = $class_schemas;
    }

    public function buildTables()
    {
        try
        {
            $tool = new ORM\Tools\SchemaTool($this->db->getDoctrine());

            echo "Re-building schemas for the following classes: <br /><br />";

            if (count($this->class_schemas) == 0) {
                $this->getClasses($this->db->path_to_entities);
            }

            foreach ($this->class_schemas as $class_schema)
            {
                echo $class_schema->name . '<br />';
            }

            if ($this->class_schemas && count($this->class_schemas) > 0) {
                $tool->dropSchema($this->class_schemas);
                $tool->createSchema($this->class_schemas);
                $this->db->getDoctrine()->flush();
            }
        }
        catch (Exception $ex)
        {
            var_dump($ex);
        }
    }

    protected function getClasses($base_directory, $class_path = '/')
    {
        $directory = $base_directory . $class_path;
        $class_names = array_diff(scandir($directory), array(".", ".."));
        foreach ($class_names as $key=>$class_name)
        {
            try
            {
                if (is_dir($directory . '/' . $class_name)) {
                    $this->getClasses($base_directory, $class_path . $class_name . '/');
                }
                else {
                    $class_name = str_replace(".php", "", $class_name);
                    if (array_search($class_name, $this->excluded_class_names) === false) {
                        $ns = rtrim($this->db->entity_namespace, '\\') . '\\' . ltrim(str_replace('/', '\\', $class_path), '\\');
                        $class_names[$key] = $class_name;
                        $this->class_schemas[] = $this->db->getDoctrine()->getClassMetadata($ns . $class_name);
                    }

                }
            }
            catch (Doctrine\ORM\Mapping\MappingException $ex)
            {
                unset($class_names[$key]); //Cannot be handled by Doctrine
            }
        }
    }

    public function populateTables()
    {
        try
        {
            $populations = array_diff(scandir('populate'), array('.', '..'));
            sort($populations);
            foreach ($populations as $population)
            {
                include_once('populate/' . $population);
                $class_name = ucwords(str_replace('.php', '', $population)) . 'Populator';
                if (!class_exists($class_name) && strpos($class_name, "_") !== false) {
                    //File name has a prefix so that things are populated in the right order
                    $class_name = substr($class_name, strpos($class_name, "_") + 1);
                }
                if (class_exists($class_name)) {
                    $populator = new $class_name($this->db->getDoctrine());
                    $populator->populate();
                }
            }
            $this->db->getDoctrine()->flush();
        }
        catch (Exception $ex)
        {
            var_dump($ex);
        }
    }

    public function validateTables()
    {
        //Make sure Doctrine is happy with all that
        $validator = new SchemaValidator($this->db->getDoctrine());
        $errors = $validator->validateMapping();
        if (count($errors) > 0) {
            foreach ($errors as $error)
            {
                if (is_array($error)) {
                    echo implode("<br />", $error);
                }
                else {
                    echo $error;
                }
            }
        }
    }
}
