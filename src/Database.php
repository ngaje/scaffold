<?php
namespace Netshine\Scaffold;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

class Database extends \PDO
{
    private $dev_mode = true;
    private $jloader_unreg = false;

    private $host = 'localhost';
    private $username = 'root';
    private $password = '';
    private $database = '';
    private $port = 3306;
    private $dsn = '';

    /** @var EntityManager **/
    protected $entity_manager;
    /** @var string **/
    public $entity_namespace;
    /** @var string **/
    public $path_to_entities;

    public function __construct($dsn = null, $username = null, $password = null, $driver_options = null, $database = null, $entity_namespace = null, $path_to_entities = null)
    {
        $this->username = $username ? $username : $this->username;
        $this->password = $password ? $password : $this->password;
        $this->database = $database ? $database : '';
        $this->dsn = $dsn ? $dsn : 'mysql:host=' . $this->host . ';dbname=' . $this->database . ';port=' . $this->port . ';charset=UTF8';
        $this->entity_namespace = $entity_namespace;
        $this->path_to_entities = $path_to_entities;
        parent::__construct($this->dsn, $this->username, $this->password, $driver_options);
    }

    public function getDoctrine()
    {
        if (!isset($this->entity_manager)) {

             // unregister JLoader from SPL autoloader
            foreach (spl_autoload_functions() as $function) {
                if (is_array($function) && ($function[0] === 'JLoader')) {
                    spl_autoload_unregister($function);
                    $this->jloader_unreg = true;
                }
            }

            //Set up Doctrine
            $doctrine_config = Setup::createAnnotationMetadataConfiguration(array($this->path_to_entities), $this->dev_mode);
            if ($this->dev_mode) {
                $doctrine_config->setAutoGenerateProxyClasses(true);
            } else {
                $doctrine_config->setAutoGenerateProxyClasses(false);
            }
            $doctrine_config->setEntityNamespaces(array($this->entity_namespace));

            $doctrine_conn = array('pdo' => $this);
            $this->entity_manager = EntityManager::create($doctrine_conn, $doctrine_config);

            //Re-register JLoader into SPL autoloader if required
            if ($this->jloader_unreg && method_exists('\JLoader', 'setup')) {
                \JLoader::setup();
            }
        }
        return $this->entity_manager;
    }

    /*public function __destruct()
    {
        //De-register Doctrine autoloader so it doesn't interfere with JLoader
        foreach (spl_autoload_functions() as $function) {
            if (is_array($function) && ($function[0] === 'Doctrine')) {
                spl_autoload_unregister($function);
            }
        }
    }*/
}