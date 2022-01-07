<?php
namespace Ngaje\Scaffold;
use View\Form;
use Pimple\Container;

class Dependencies
{
    /** @var Container **/
    protected $container;
    /** @var RoutingConfig **/
    protected $routing_config;
    /** @var string **/
    protected $class_suffix;
    /** @var string **/
    protected $resource;
    /** @var string **/
    protected $method;

    public function __construct(Container $container, RoutingConfig $routing_config, $class_suffix, $resource, $method = 'get')
    {
        $this->container = $container;
        $this->routing_config = $routing_config;
        $this->class_suffix = $class_suffix;
        $this->resource = $resource;
        $this->method = $method; //Can be used by sub classes if they want to change the implementation depending on the action
        $this->initialise();
    }

    protected function initialise() {}

    public function defineDependencies()
    {
        $this->defineResourceForm();
        $this->defineResourceView();
        $this->defineResourceService();
        $this->defineResourceController();

        return $this->container;
    }

    protected function defineResourceForm()
    {
        $class_name = $this->guessQualifiedClassName('view', 'Form');
        $resource = $this->resource;
        $field_namespace = $this->routing_config->namespace_form_fields;

        if (class_exists($class_name)) {
            $this->container['form'] = function ($c) use ($class_name, $resource) {
                return new $class_name($c['field_factory'], $c['cms'], $c['request']->language, $resource, 'post');
            };

            $class_name = $this->guessQualifiedClassName('view', 'FormRenderer');
            if (!class_exists($class_name)) {
                $class_name = '\\Netshine\\Scaffold\\View\\Form\\FormRenderer';
            }
            $this->container['form_renderer'] = function ($c) use ($class_name) {
                return new $class_name($c['form'], $this->routing_config->site_entry_url);
            };

            $class_name = $this->guessQualifiedClassName('view', 'FormMapper');
            if (!class_exists($class_name)) {
                $class_name = '\\Netshine\\Scaffold\\View\\Form\\FormMapper';
            }
            $this->container['form_mapper'] = function ($c) use ($class_name) {
                return new $class_name($c['request'], $c['form']);
            };

            $class_name = $this->guessQualifiedClassName('view', 'FieldFactory');
            if (!class_exists($class_name)) {
                $class_name = '\\Netshine\\Scaffold\\View\\Form\\FieldFactory';
            }
            $this->container['field_factory'] = function ($c) use ($class_name, $field_namespace) {
                return new $class_name($c['request']->language, $field_namespace, $c['cms']);
            };
        }
    }

    protected function defineResourceView()
    {
        $class_name = $this->guessQualifiedClassName('view', 'View');
        if (class_exists($class_name)) {
            $this->container['view'] = function ($c) use ($class_name) {
                return new $class_name($c['cms'], $c['request']->language, $c->offsetExists('form') ? $c['form'] : null, $c->offsetExists('form_renderer') ? $c['form_renderer'] : null, $c->offsetExists('field_factory') ? $c['field_factory'] : null);
            };
        }
    }

    protected function defineResourceService($mapper_args = array(), $service_args = array())
    {
        $mapper_class_name = $this->guessQualifiedClassName('model', 'Mapper\\DataMapper');
        $service_class_name = $this->guessQualifiedClassName('model', 'Service\\Service');

        if (class_exists($service_class_name) && !class_exists($mapper_class_name)) {
            //Give the service a vanilla mapper
            $mapper_class_name = '\Netshine\\Scaffold\\Model\\DataMapperBase';
        }

        if (class_exists($mapper_class_name)) {
            $this->container['data_mapper'] = function ($c) use ($mapper_class_name, $mapper_args) {
                array_unshift($mapper_args, $c['request']->language);
                array_unshift($mapper_args, $c['pagination']);
                array_unshift($mapper_args, $c['db']);
                $reflector = new \ReflectionClass($mapper_class_name);
                return $reflector->newInstanceArgs($mapper_args);
            };
        }

        if (class_exists($service_class_name)) {
            $this->container['service'] = function ($c) use ($service_class_name, $service_args) {
                array_unshift($service_args, $c['request']->language);
                array_unshift($service_args, $c->offsetExists('data_mapper') ? $c['data_mapper'] : null);
                array_unshift($service_args, $c['cms']);
                $reflector = new \ReflectionClass($service_class_name);
                return $reflector->newInstanceArgs($service_args);
            };
        }
    }

    protected function defineResourceController()
    {
        $class_name = $this->guessQualifiedClassName('controller', 'Controller');
        if (class_exists($class_name)) {
            $this->container['controller'] = function ($c) use ($class_name) {
                $controller = new $class_name($c['cms'], $c['request'], isset($c['view']) ? $c['view'] : null, isset($c['form']) ? $c['form'] : null, isset($c['service']) ? $c['service'] : null);
                if (isset($c['form_mapper'])) {
                    $controller->setFormMapper($c['form_mapper']);
                }
                return $controller;
            };
        }
    }

    protected function guessQualifiedClassName($namespace_type, $class_prefix)
    {
        $class_name = $this->routing_config->{'namespace_' . $namespace_type} . $class_prefix . $this->class_suffix;
        if (!class_exists($class_name)) {
            $class_name = $this->routing_config->{'namespace_' . $namespace_type} . $this->class_suffix . '\\' . $class_prefix . $this->class_suffix;
        }
        if (!class_exists($class_name)) {
            //See if we have a vanilla version
            $class_name = $this->routing_config->{'namespace_' . $namespace_type} . $class_prefix;
            if (!class_exists($class_name)) {
                $class_name = $this->routing_config->{'namespace_' . $namespace_type} . 'Form\\' . $class_prefix;
                if (!class_exists($class_name)) {
                    $class_name = '\\Netshine\\Scaffold\\' . ucwords($namespace_type) . '\\' . $class_prefix . $this->class_suffix;
                    if (!class_exists($class_name)) {
                        $class_name = '\\Netshine\\Scaffold\\' . ucwords($namespace_type) . '\\' . $this->class_suffix . '\\' . $class_prefix . $this->class_suffix;
                    }
                }
            }
        }
        return $class_name;
    }
}
