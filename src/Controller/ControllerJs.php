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
                $this->replaceTokens($file);
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

    protected function replaceTokens(File $file)
    {
        $loop_breaker = 0;
        $contents = $file->getContents();
        $token_pos = strpos($contents, '[[');

        while($token_pos !== false) {
            $loop_breaker++;
            if ($loop_breaker > 200) {
                break;
            }
            $token_end = strpos($contents, ']]', $token_pos);
            $language_resource = trim(substr($contents, $token_pos + 2, $token_end - ($token_pos + 2)));
            list($resource, $entry) = explode(":", $language_resource);
            $language_data = $this->request->language->{$resource}[$entry];
            $contents = substr($contents, 0, $token_pos) . $language_data . substr($contents, $token_end + 2);
            $token_pos = strpos($contents, '[[');
        }

        $token_pos = strpos($contents, '<' . '?php ');
        while($token_pos !== false) {
            $loop_breaker++;
            if ($loop_breaker > 200) {
                break;
            }
            $token_end = strpos($contents, '?' . '>', $token_pos + 1);
            $php_code = trim(substr($contents, $token_pos + 6, $token_end - ($token_pos + 6)));
            ob_start();
            $result = eval($php_code);
            $output = ob_get_clean();
            $output = is_string($result) && strlen($result) > 0 && $output == '' ? $result : $output;
            $contents = substr($contents, 0, $token_pos) . $output . substr($contents, $token_end + 2);
            $token_pos = strpos($contents, '<' . '?php ');
        }
        $file->tempSetContents($contents);
    }
}