<?php
namespace Netshine\Scaffold\View\Form\FieldTypes;

use Netshine\Scaffold\View\Form\FieldBase;

class FieldLinkButton extends FieldBase
{
    /** @var string **/
    public $url;
    /** @var string **/
    public $image;

    public function validate()
    {
        if (strlen($this->url) > 0 && @$_REQUEST[$this->name]) { //Button clicked, but javascript disabled
            if (!headers_sent()) {
                //Clear the buffers
                $loopbreaker = 0;
                while (ob_get_length() !== false)
                {
                    $loopbreaker++;
                    @ob_end_clean();
                    if ($loopbreaker > 15) {
                        break;
                    }
                }

                //Do the redirect
                header('Location: ' . $this->url, true, 301);
                exit;
            }
        }
        return true;
    }
}