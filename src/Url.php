<?php
namespace Ngaje\Scaffold;

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
    public $full_url;

    /** @var string **/
    protected static $cache;

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
        $querystring = $this->query ? explode('&', $this->query) : array();
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

    public function get($timeout = 20, $force_refresh = false)
    {
        $contents = false;
        if (strlen($this->getFullUrl()) > 0) {
            if (!$force_refresh) {
                if (isset(self::$cache) && strlen(self::$cache) > 0) {
                    return self::$cache;
                }
            }
            if (ini_get('allow_url_fopen') == '1') {
                $contents = $this->getUsingFile($timeout);
            }
            if ($contents === false && function_exists('curl_init')) {
                $contents = $this->getUsingCurl($timeout);
            }
            if ($contents === false) {
                $contents = $this->getUsingSockets($timeout);
            }

            self::$cache = $contents;
        }
        return $contents;
    }

    protected function getUsingFile($timeout)
    {
        $options = array(
                'http'=>array(
                'method'=>"GET",
                'timeout'=>$timeout,
                'header'=>"Accept-language: en\r\n"
                )
        );
        $context = stream_context_create($options);
        return file_get_contents($this->getFullUrl(), null, $context);
    }

    protected function getUsingCurl($timeout)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getFullUrl());
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); //Allow redirects
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); //Return into a variable
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPGET, 0); //use GET
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    protected function getUsingSockets($timeout)
    {
        $url_parts = parse_url($this->getFullUrl());
        if (!isset($url_parts['path'])) {
            $url_parts['path'] = '/';
        }
        if (!isset($url_parts['query'])) {
            $url_parts['query' ] = '';
        }

        $header = "Host: " . $url_parts['host'] . "\r\n";
        $header .= "User-Agent: Mozilla\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . strlen($url_parts['query']) . "\r\n";
        $header .= "Connection: close\r\n\r\n";

        $errno = '';
        $errstr = '';
        $fp = @fsockopen($url_parts['host'], 80, $errno, $errstr, $timeout);
        if ($fp) {
            $out = '';
            stream_set_timeout($fp, $timeout);
            fputs($fp, "GET " . $url_parts['path'] . "  HTTP/1.1\r\n");
            fputs($fp, $header. $url_parts['query']);
            fwrite($fp, $out);
            $info = stream_get_meta_data($fp);
            $result = "";
            if ($info['timed_out']) {
                @fclose($fp);
                return false;
            }
            while (!feof($fp)) {
                $info = stream_get_meta_data($fp);
                if ($info['timed_out']) {
                    @fclose($fp);
                    return false;
                }
                $result .= fread($fp, 4096);
            }
            fclose($fp);
            if (strlen($result) > 0) {
                //Strip the HTTP headers.
                $pos = strpos($result, "\r\n\r\n");
                if ($pos !== false) {
                    $result = substr($result, $pos + 4);
                }
                return $result;
            } else {
                return false;
            }
        }
    }
}
