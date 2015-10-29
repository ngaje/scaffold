<?php
namespace Netshine\Scaffold;

use Pimple\Container;

class Router
{
    /** @var ICms **/
    protected $cms;
    /** @var RoutingConfig **/
    protected $routing_config;
    /** @var DatabaseConfig **/
    protected $db_config;
    /** @var Request **/
    protected $request;
    /** @var Container **/
    protected $container;

    /**
    * @param ICms $cms
    * @param RoutingConfig $routing_config
    * @param DatabaseConfig $db_config
    * @param mixed $dev_mode Whether or not we are in development mode. Leave at default (null) to automatically detect, or pass in true or false
    * @param string $cache One of: 'memcached', 'apc', 'redis', 'array'. Only implemented if dev_mode is false (when in dev mode, array cache is always used)
    */
    public function __construct(ICms $cms, RoutingConfig $routing_config, DatabaseConfig $db_config = null, $dev_mode = null, $cache = 'memcached')
    {
        $this->cms = $cms;
        $this->routing_config = $routing_config;
        $this->db_config = $db_config;
        $language = $this->initialiseLanguage();
        $this->loadRequest($language);
        if (!$this->request->resource) {
            $this->request->resource = $routing_config->default_resource;
        }
        $this->container = new Container();
        $this->defineDependencies($dev_mode, $cache);
    }

    public function loadRequest(Language $language)
    {
        $this->request = new Request($language);
        $this->request->is_bare_request = (strpos($this->request->url, $language->routing['bare_entry_url']) !== false);
    }

    public function initialiseLanguage()
    {
        $language = new Language($this->routing_config->language_path);
        $language->routing['entry_url'] = $this->routing_config->site_entry_url->getFullUrl();
        $language->routing['bare_entry_url'] = $this->routing_config->site_bare_entry_url->getFullUrl();
        return $language;
    }

    /**
    * @param mixed $dev_mode Whether or not we are in development mode. Leave at default (null) to automatically detect, or pass in true or false
    * @param string $cache One of: 'memcached', 'apc', 'redis', 'array'. Only implemented if dev_mode is false (when in dev mode, array cache is always used)
    */
    protected function defineDependencies($dev_mode = null, $cache = 'memcached')
    {
        $this->container['cms'] = $this->cms;
        $this->container['request'] = $this->request;
        $db_config = $this->db_config;
        if ($dev_mode === null) {
            $dev_mode = strpos($this->request->url->full_url, 'http://localhost') !== false || strpos($this->request->url->full_url, 'http://dev') !== false;
        } else {
            $dev_mode = $dev_mode ? true : false;
        }

        $this->container['db'] = function ($c) use ($db_config, $dev_mode) {
            $db = new Database($db_config->dsn, $db_config->host, $db_config->username, $db_config->password, null, $db_config->database, $db_config->entity_namespace, $db_config->entity_path);
            $db->setDevMode($dev_mode);
            return $db;
        };
        $this->container['pagination'] = function ($c) {
            return new Pagination($c['cms'], 25, 0, 1, 15);
        };

        //Try to find a dependency definition class for the current resource (base class will guess what is needed based on resource name and which classes exist for that resource)
        $class_suffix = str_replace(' ', '', ucwords(str_replace('_', ' ', $this->request->resource)));
        $class_name = $this->routing_config->namespace_dependencies . 'Dependencies' . $class_suffix;
        if (!class_exists($class_name)) {
            $class_name = $this->routing_config->namespace_dependencies . $class_suffix . '\Dependencies' . $class_suffix;
            if (!class_exists($class_name)) {
                $class_name = $this->routing_config->namespace_dependencies . 'Dependencies';
                if (!class_exists($class_name)) {
                    $class_name = 'Netshine\Scaffold\Dependencies';
                }
            }
        }

        $dependencyConfig = new $class_name($this->request, $this->container, $this->routing_config, $class_suffix, $this->request->resource, $this->request->method);
        $this->container = $dependencyConfig->defineDependencies();
    }

    public function route()
    {
        if ($this->container->offsetExists('controller')) {
            $controller = $this->container['controller'];
            $controller->executeMethod();
            unset($controller); //Destroy all the objects
        } else {
            $view = new View\ViewNotFoundHtml($this->container['cms'], $this->container['request']->language);
            $view->renderNotFound();
        }
    }
}