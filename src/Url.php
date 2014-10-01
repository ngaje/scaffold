<?php
namespace Netshine\Scaffold;

class Url
{
    public $scheme;
    public $host;
    public $port;
    public $user;
    public $pass;
    public $path;
    public $query;
    public $fragment;

    public function __construct($url = null)
    {
        if ($url) {
            $url = filter_var($url, FILTER_VALIDATE_URL);
        }
        if (!$url) {
            $url = $this->getCurrentUrl();
        }
        $this->full_url = $url;
        $url_parts = parse_url($url);
        foreach ($url_parts as $key=>$value)
        {
            $this->$key = $value;
        }
    }

    protected function getCurrentUrl()
    {
        return sprintf("%s://%s%s", isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http', $_SERVER['SERVER_NAME'], $_SERVER['REQUEST_URI']);
    }

    public function getFullUrl()
    {
        $url = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http';
        if (isset($this->scheme) && strlen($this->scheme) > 0) {
            $url = $this->scheme;
        }
        $url .= '://';
        if (isset($this->user) && $this->user && isset($this->pass) && $this->pass) {
            $url .= $this->user . ':' . $this->pass . '@';
        }
        $url .= $this->host;
        if (isset($this->port) && $this->port) {
            $url .= ':' . $this->port;
        }
        if (isset($this->path) && $this->path) {
            $url .= $this->path;
        }
        if (isset($this->query) && $this->query) {
            $url .= '?' . $this->query;
        }
        return $url;
    }

    public function __toString()
    {
        return $this->getFullUrl();
    }

    /**
    * @param array $keys
    */
    public function removeQuerystringParams($keys)
    {
        $querystring = explode('&', $this->query);
        $new_querystring = array();
        foreach ($querystring as $query_pair)
        {
            $key = explode('=', $query_pair)[0];
            if (array_search($key, $keys) === false) {
                $new_querystring[] = $query_pair;
            }
        }
        $this->query = implode('&', $new_querystring);
    }
}