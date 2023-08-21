<?php
namespace Ngaje\Scaffold;

class LanguageResource implements \ArrayAccess
{
    /** @var Language **/
    protected $parent_language;
    protected $resource_name;
    protected $resource_elements = array();

    /**
    * @param Language $parent
    * @param string $resource
    * @param array $elements
    * @return LanguageResource
    */
    public function __construct(Language $parent, $resource, $elements = array())
    {
        $this->parent_language = $parent;
        $this->resource_name = $resource;
        $this->resource_elements = $elements;
    }
    public function offsetSet($offset, $value):void
    {
        if (is_null($offset)) {
            $this->resource_elements[] = $value;
        } else {
            $this->resource_elements[$offset] = $value;
        }
    }
    public function offsetExists($offset):bool
    {
        return isset($this->resource_elements[$offset]);
    }
    public function offsetUnset($offset):void
    {
        unset($this->resource_elements[$offset]);
    }
    public function offsetGet($offset):mixed
    {
        static $depth = 1; //Protect against circular references

        $value = "";
        $sprintf_tokens = array();
        if ($offset && !isset($this->resource_elements[$offset]) && strpos($offset, '%') !== false) {
            //Extract sprintf tokens
            $sprintf_tokens = explode('%', $offset);
            $offset = array_shift($sprintf_tokens);
        }
        if (isset($this->resource_elements[$offset])) {
            $depth++;
            if ($depth < 10) {
                $value = $this->resource_elements[$offset];
                //Resolve any tokens that point to other language elements
                $tokens = $this->findTokens($value);
                foreach($tokens as $token)
                {
                    $parts = explode(':', $token);
                    if (count($parts) == 2) {
                        $token_value = $this->parent_language->{$parts[0]}[$parts[1]];
                        $value = str_replace('[[' . $token . ']]', $token_value, $value);
                    }
                }
            }
            else {
                //Circular reference!
                throw new Exception("CIRCULAR REFERENCE: " . $this->resource_name . "[" . $offset . "]");
            }
            $depth--;
        }

        if (count($sprintf_tokens) > 0) {
            $orig_value = $value;
            array_unshift($sprintf_tokens, $value);
            $value = @call_user_func_array('sprintf', $sprintf_tokens);
            $value = $value ? $value : $orig_value;
        }

        return isset($this->resource_elements[$offset]) ? $value : null;
    }

    public function getAll()
    {
        return $this->resource_elements;
    }

    /**
    * Find any tokens (wrapped in double square brackets with a colon separating resource from element
    *  - eg. "[[branding:name]]" ) in the given value and return them as an array
    * @param string $value
    * @param array $tokens For recursive use in case several tokens appear in a single entry
    * @return array
    */
    protected function findTokens($value, $tokens = array())
    {
        $token_start = strpos($value, '[[');
        if ($token_start !== false) {
            $token_end = strpos($value, ']]', $token_start);
            if ($token_end !== false) {
                $token = @substr($value, $token_start + 2, ($token_end - $token_start) - 2);
                if (strlen($token) > 0 && strpos($token, ':') !== false) {
                    $tokens[] = $token;
                }
                $tokens = $this->findTokens(substr($value, $token_end), $tokens);
            }
        }
        return $tokens;
    }
}
