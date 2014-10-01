<?php
namespace Netshine\Scaffold\Controller;

use Netshine\Scaffold\File;

class ControllerCss extends ControllerBase
{
    public function get()
    {
        $full_file_name = $this->getCssFileName();
        $file = new File($full_file_name, 'text/css');
        if ($file->validateFileExtension(array('.css', '.less'), '.css') && $file->exists()) {
            $this->embedImages($file);
            $file->doDownload();
            exit;
        } else {
            header('HTTP/1.0 404 not found');
        }
    }

    protected function getCssFileName()
    {
        $css_file = $this->request->getRequestParam('id');
        if (strpos($css_file, '.') === false) {
            $css_file .= '.css';
        }

        $full_file_name = realpath(dirname(__FILE__) . '/..') . '/View/css/' . $css_file;
        if (!file_exists($full_file_name)) {
            //Try within a js folder, in case it is local to a script
            $full_file_name = realpath(dirname(__FILE__) . '/..') . '/View/js/' . $css_file;
        }
        return $full_file_name;
    }

    protected function getImageFullFileName($file_name)
    {
        $image_file_name = realpath(dirname(__FILE__) . '/..') . '/View/images/' . $file_name;
        if (!file_exists($image_file_name)) {
            $image_file_name = realpath(dirname(__FILE__) . '/..') . '/View/js/' . $file_name;
            if (!file_exists($image_file_name)) {
                $image_file_name = realpath(dirname(__FILE__) . '/..') . '/View/js/images/' . $file_name;
            }
        }
        return $image_file_name;
    }

    protected function embedImages(File &$file)
    {
        $loop_breaker = 0;
        $contents = $file->getContents();
        $image_pos = strpos($contents, 'image://');

        while($image_pos !== false) {
            $loop_breaker++;
            if ($loop_breaker > 200) {
                break;
            }
            $image_end = strpos($contents, ')', $image_pos);
            $file_name = trim(substr($contents, $image_pos + 8, $image_end - ($image_pos + 8)));
            $full_file_name = $this->getImageFullFileName($file_name);
            $image_data = '';
            if (file_exists($full_file_name)) {
                $image_data = base64_encode(file_get_contents($full_file_name));
            }
            $contents = substr($contents, 0, $image_pos) . 'data:image/png;base64,' . $image_data . substr($contents, $image_end);
            $image_pos = strpos($contents, 'image://');
        }
        $file->tempSetContents($contents);
    }
}