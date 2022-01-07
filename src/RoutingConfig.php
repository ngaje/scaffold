<?php
namespace Ngaje\Scaffold;

class RoutingConfig
{
    /** @var Url **/
    public $site_entry_url = null;
    /** @var Url **/
    public $site_bare_entry_url = null;
    public $namespace_dependencies = null;
    public $namespace_model = null;
    public $namespace_view = null;
    public $namespace_controller = null;
    public $namespace_form_fields = null;
    public $language_path = null;
    public $default_resource = null;

    public function __construct(Url $site_entry_url = null, Url $site_bare_entry_url = null, $namespace_dependencies = null, $namespace_model = null, $namespace_view = null, $namespace_controller = null, $namespace_form_fields = null, $language_path = null, $default_resource = null)
    {
        //If a sub class overrides this, we might have our properties already populated with default values
        $this->site_entry_url = $site_entry_url === null ? $this->site_entry_url : $site_entry_url;
        $this->site_bare_entry_url = $site_bare_entry_url === null ? $this->site_bare_entry_url : $site_bare_entry_url;
        $this->namespace_dependencies = $namespace_dependencies === null ? $this->namespace_dependencies : $this->applyNamespaceBackslash($namespace_dependencies);
        $this->namespace_model = $namespace_model === null ? $this->namespace_model : $this->applyNamespaceBackslash($namespace_model);
        $this->namespace_view = $namespace_view === null ? $this->namespace_view : $this->applyNamespaceBackslash($namespace_view);
        $this->namespace_controller = $namespace_controller === null ? $this->namespace_controller : $this->applyNamespaceBackslash($namespace_controller);
        $this->namespace_form_fields = $namespace_form_fields === null ? $this->namespace_form_fields : $this->applyNamespaceBackslash($namespace_form_fields);
        $this->default_resource = $default_resource === null ? $this->default_resource : $default_resource;
        $this->language_path = $language_path === null ? $this->language_path : $language_path;

        if ($this->site_entry_url === null ||
            $this->site_bare_entry_url === null ||
            $this->namespace_dependencies === null ||
            $this->namespace_model === null ||
            $this->namespace_view === null ||
            $this->namespace_controller === null ||
            $this->namespace_form_fields === null ||
            $this->default_resource === null ||
            $this->language_path === null) {
                throw new \Exception('Mandaotry parameters not set in RoutingConfig');
        }

        $this->language_path = rtrim($this->language_path, '/\\');
        if (strlen($this->language_path) == 0 || !file_exists($this->language_path) || !is_dir($this->language_path)) {
            throw new \Exception('Path to language files does not exist!');
        }

    }

    protected function applyNamespaceBackslash($namespace)
    {
        if (strlen($namespace) > 0 && substr($namespace, strlen($namespace) - 1) != '\\') {
            $namespace .= '\\';
        }
        return $namespace;
    }
}
