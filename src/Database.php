<?php
namespace Netshine\Scaffold;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Proxy\AbstractProxyFactory;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

class Database extends \PDO
{
    private $dev_mode = true;
    private $doctrine_cache = null;
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

    public function __construct($dsn = null, $host = null, $username = null, $password = null, $driver_options = null, $database = null, $entity_namespace = null, $path_to_entities = null)
    {
        $this->username = $username ? $username : $this->username;
        $this->password = $password ? $password : $this->password;
        $this->database = $database ? $database : '';
        $this->host = $host ? $host : $this->host;
        $this->dsn = $dsn ? $dsn : 'mysql:host=' . $this->host . ';dbname=' . $this->database . ';port=' . $this->port . ';charset=UTF8';
        $this->entity_namespace = $entity_namespace;
        $this->path_to_entities = $path_to_entities;
        parent::__construct($this->dsn, $this->username, $this->password, $driver_options);
    }

    public function __destruct()
    {
        $this->close();
    }

    public function close()
    {
        if (isset($this->entity_manager)) {
            $this->entity_manager->close();
            unset($this->entity_manager);
        }
    }

    public function setDevMode($value, $set_cache = true, $cache = 'memcached')
    {
        $this->dev_mode = $value ? true : false;
        if ($set_cache) {
            $this->setDoctrineCache($value ? 'array' : $cache);
        }
    }

    public function setDoctrineCache($value)
    {
        switch ($value) {
            case 'memcached':
                if (class_exists('\Memcached')) {
                    $this->doctrine_cache = new \Doctrine\Common\Cache\MemcachedCache();
                    $mc = new \Memcached('hrmagik_mc');
                    $mc->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
                    if (!count($mc->getServerList())) {
                        $mc->addServers(array(
                            array('localhost',11211)
                        ));
                    }
                    $this->doctrine_cache->setMemcached($mc);
                } else {
                    $this->doctrine_cache = new \Doctrine\Common\Cache\ArrayCache();
                }
                break;
            case 'apc':
                $this->doctrine_cache = new \Doctrine\Common\Cache\ApcCache();


                break;
            case 'redis':
                $this->doctrine_cache = new \Doctrine\Common\Cache\RedisCache();


                break;
            default:
            case 'array':
                $this->doctrine_cache = new \Doctrine\Common\Cache\ArrayCache();


                break;
        }

        if (isset($this->entity_manager)) {
            $this->restartDoctrine();
        }
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
            $doctrine_config = Setup::createAnnotationMetadataConfiguration(array($this->path_to_entities), $this->dev_mode, null, $this->doctrine_cache);
            if ($this->dev_mode) {
                $doctrine_config->setAutoGenerateProxyClasses(true);
            } else {
                $doctrine_config->setAutoGenerateProxyClasses(AbstractProxyFactory::AUTOGENERATE_FILE_NOT_EXISTS);
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

    public function restartDoctrine()
    {
        $enabled_filters = array();
        if (isset($this->entity_manager)) {
            $filters = $this->entity_manager->getFilters();
            foreach ($filters->getEnabledFilters() as $filter_name=>$filter) {
                $filter_class = get_class($filter);
                $enabled_filters[$filter_name] = array($filter_class=>$filter->__toString());
            }
            @$this->entity_manager->close();
            unset($this->entity_manager);
        }
        $em = $this->getDoctrine();
        foreach ($enabled_filters as $filter_name=>$filter) {
            foreach ($filter as $filter_class=>$filter_params_string) {
                $em->getConfiguration()->addFilter($filter_name, $filter_class);
                $this_filter = $em->getFilters()->enable($filter_name);
                $filter_params = @unserialize($filter_params_string);
                if ($filter_params) {
                    foreach ($filter_params as $filter_param=>$filter_value) {
                        if (isset($filter_value['value'])) {
                            $this_filter->setParameter($filter_param, $filter_value['value']);
                        }
                    }
                }
            }
        }
    }
}