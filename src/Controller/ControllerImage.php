<?php
namespace Netshine\Scaffold\Controller;

use Netshine\Scaffold\File;

class ControllerImage extends ControllerBase
{
    public function get()
    {
        $full_file_name = $this->getFileName();
        $file = new File($full_file_name, 'image/' . pathinfo($full_file_name, PATHINFO_EXTENSION));
        if ($file->validateFileExtension() && $file->exists()) {
            $file->doDownload();
            exit;
        } else {
            header('HTTP/1.0 404 not found');
            exit;
        }
    }

    protected function getFileName()
    {
        $image_file = $this->request->getRequestParam('id');
        if (strpos($image_file, '.') === false) {
            $image_file .= '.png';
        }

        $full_file_name = realpath(dirname(__FILE__) . '/..') . '/View/images/' . $image_file;
        if (!file_exists($full_file_name)) {
            //Try within a js folder, in case it is local to a script
            $full_file_name = realpath(dirname(__FILE__) . '/..') . '/View/js/' . $image_file;
        }
        return $full_file_name;
    }
}