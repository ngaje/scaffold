<?php
namespace Netshine\Scaffold;

class Request
{
    /** @var string **/
    public $resource = '';
    /** @var string **/
    public $method = '';
    /** @var string **/
    public $content_return_type = 'html';
    /** @var Language **/
    public $language;
    /** @var Url **/
    public $url;
    /** @var boolean **/
    public $is_bare_request = false;

    /**
    * @param string $language_path Path to language files
    * @return Request
    */
    public function __construct(Language $language)
    {
        $this->url = new Url();
        $this->resource = $this->getRequestParam('resource');
        $this->method = $this->getRequestParam('method');
        if ($this->method === null && count($_POST)) {
            $this->method = 'post';
        }
        $this->language = $language;
        $this->negotiateLanguage();
        $this->negotiateType();
        $this->fileTypeOverride();
        $this->validateType();
    }

    public function negotiateLanguage($profile_id = null)
    {
        if ($profile_id) {
            $this->language->profile_id = $profile_id;
        }
        $lang = "en-GB";
        if ($this->getHeader('Accept-Language', false)) {
            $negotiator = new \Negotiation\Negotiator();
            $best = $negotiator->getBest($this->getHeader('Accept-Language'), $this->language->getAllLanguageStrings());
            if ($best) {
                $lang = $best->getValue();
            }
        }
        $this->language->setLanguage($lang, $profile_id);
    }

    protected function negotiateType()
    {
        $this->content_return_type = 'html'; //Default
        if ($this->getHeader('ACCEPT', false)) {
            $negotiator = new \Negotiation\FormatNegotiator();
            $acceptable_types = array('html', '*/*');
            $acceptable_types = array('html', 'json', 'xml', '*/*');
            $this->content_return_type = $negotiator->getBestFormat($this->getHeader('ACCEPT'), $acceptable_types);
            if ($this->content_return_type == '*/*') {
                $this->content_return_type = 'html';
            }
        }
    }

    /**
    * If file name in URL specifies a file type, allow that to override the value sent in the header, if supported
    */
    protected function fileTypeOverride()
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            $this->request_url = $_SERVER['REQUEST_URI'];
            $request_parts = parse_url($_SERVER['REQUEST_URI']);
            if (isset($request_parts['path'])) {
                $path_info = pathinfo($request_parts['path']);
                if (isset($path_info['extension'])) {
                    switch ($path_info['extension'])
                    {
                        case "json":
                        case "xml":
                            $this->content_return_type = $path_info['extension'];
                            break;
                    }
                }
            }
        }
    }

    /**
    * Make sure we support the return type
    */
    protected function validateType()
    {
        switch ($this->content_return_type)
        {
            case "html":
            case "json":
            case "xml":
                //OK
                break;
            default:
                $this->content_return_type = "html";
                break;
        }
    }

    public function getClientIpAddress()
    {
        $ip = "";
        if (isset($_SERVER)) {
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
                if (strpos($ip, ",") !== false) {
                  $ip_array = explode(",", $ip);
                  $ip = $ip_array[0];
                }
            } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                $ip = $_SERVER["HTTP_CLIENT_IP"];
            } else {
                $ip = $_SERVER["REMOTE_ADDR"];
            }
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR')) {
                $ip = getenv('HTTP_X_FORWARDED_FOR');
                if (strpos($ip, ",") !== false) {
                  $ip_array = explode(",", $ip);
                  $ip = $ip_array[0];
                }
            } else if (getenv('HTTP_CLIENT_IP')) {
                $ip = getenv('HTTP_CLIENT_IP');
            } else {
                $ip = getenv( 'REMOTE_ADDR' );
            }
        }
        return $ip;
    }

    public function getHeader($param, $default_value = null)
    {
        $headers = array();
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        }
        return $this->getArrayParam($headers, $param, $default_value);
    }

    public function getRequestFilters()
    {
        $filters = array();
        foreach ($_REQUEST as $key=>$value)
        {
            if (substr($key, 0, 7) == 'filter_' && $key != 'filter_go' && $key != 'filter_reset') {
                $filters[substr($key, 7)] = filter_var($value, FILTER_SANITIZE_STRING);
            }
        }
        return $filters;
    }

    public function getRequestParam($param, $default_value = null)
    {
        return $this->getArrayParam($_REQUEST, $param, $default_value);
    }

    public function getPostParam($param, $default_value = null)
    {
        return $this->getArrayParam($_POST, $param, $default_value);
    }

    public function getCookie($param, $default_value = null)
    {
        return $this->getArrayParam($_COOKIE, $param, $default_value);
    }

    protected function getArrayParam($array, $param, $default_value = null)
    {
        if(array_key_exists($param, $array)) {
            return $array[$param];
        }
        else {
            //Try case insensitive
            $uc_array = array_change_key_case($array, CASE_LOWER);
            if (array_key_exists(strtolower($param), $uc_array)) {
                return $uc_array[strtolower($param)];
            }
        }
        if (!is_string($default_value) && is_callable($default_value)) {
            return $default_value();
        }
        return $default_value;
    }

    public function getRawRequest()
    {
        return $_REQUEST;
    }
}