<?php
namespace Netshine\Scaffold\Controller;

use Netshine\Scaffold\File;

class ControllerJs extends ControllerBase
{
    public function get()
    {
        $full_file_name = $this->getFileName();
        $file = new File($full_file_name, 'text/javascript');
        if ($file->validateFileExtension(array('.js', '.javascript', '.css'), '.js') && $file->exists()) {
            if ($this->request->getRequestParam('tokenized')) {
                $file->replaceLanguageAndPHPTokens($this->request->language);
            }
            if (pathinfo($full_file_name, PATHINFO_EXTENSION) == 'css') {
                $file->setContentType('text/css');
            }
            $file->doDownload();
            exit;
        } else {
            header('HTTP/1.0 404 not found');
        }
    }

    protected function getFileName()
    {
        $js_file = $this->request->getRequestParam('id');
        if (strpos($js_file, '.') === false) {
            $js_file .= '.js';
        }

        $full_file_name = realpath(dirname(__FILE__) . '/..') . '/View/js/' . $js_file;
        return $full_file_name;
    }
}