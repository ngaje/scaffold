<?php
namespace Netshine\Scaffold;

class File
{
    /** @var string **/
    protected $file_name = '';
    /** @var string **/
    protected $content_type = 'application/octet-stream';
    /** @var string **/
    protected $contents = '';

    protected $handle;

    public function __construct($file_name, $content_type = 'application/octet-stream')
    {
        $this->file_name = $file_name;
        $this->content_type = $content_type;
    }

    public function setContentType($content_type)
    {
        $this->content_type = $content_type;
    }

    public function validateFileExtension($allowed_extensions = array('.png', '.gif', '.jpg', '.jpeg'), $default_extension = '.png')
    {
        if (strpos($this->file_name, '.') !== false) {
            $extension = substr($this->file_name, strrpos($this->file_name, '.'));
            if (array_search(strtolower($extension), $allowed_extensions) !== false) {
                return true;
            }
            return false;
        } else {
            $this->file_name .= $default_extension;
            return true;
        }
    }

    public function exists()
    {
        clearstatcache();
        return strlen($this->file_name) > 0 && file_exists($this->file_name);
    }

    public function getContents()
    {
        $this->contents = file_get_contents($this->file_name);
        return $this->contents;
    }

    public function putContents()
    {
        return file_put_contents($this->file_name, $this->contents);
    }

    public function open($mode = 'r')
    {
        $this->close();
        $this->handle = fopen($this->file_name, $mode);
    }

    public function readLine()
    {
        if (!isset($this->handle)) {
            $this->open();
        }

        if ($this->content_type == 'text/csv') {
            $result = fgetcsv($this->handle);
        } else {
            $result = fgets($this->handle);
        }

        if ($result === false) {
            $this->close();
        }
        return $result;
    }

    public function close()
    {
        if (isset($this->handle)) {
            @fclose($this->handle);
        }
    }

    public function tempSetContents($contents)
    {
        $this->contents = $contents;
    }

    public function doDownload()
    {
        //Download the file...
        $loopbreaker = 0;
        while (ob_get_length() !== false)
        {
            $loopbreaker++;
            @ob_end_clean();
            if ($loopbreaker > 15)
            {
                break;
            }
        }

        //Required for IE, otherwise Content-disposition is ignored
        if(ini_get('zlib.output_compression')) {@ini_set('zlib.output_compression', 'Off');};
        header("Pragma: public"); // required
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false); // required for certain browsers
        header("Content-Type: " . $this->content_type);
        header("Content-Disposition: attachment; filename=\"" . basename($this->file_name) . "\";" );
        header("Content-Transfer-Encoding: binary");
        clearstatcache();
        if (strlen($this->contents) > 0) {
            header("Content-Length: ". strlen($this->contents));
            echo $this->contents;
        } else {
            header("Content-Length: ". @filesize($this->file_name));
            $this->readfile_chunked();
        }
        exit;
    }

    protected function readfile_chunked()
    {
        //Based on unlicensed code in public domain from php.net user comments
        $chunksize = 1*(1024*1024); // how many bytes per chunk
        $buffer = '';
        $count =0;

        $handle = fopen($this->file_name, 'rb');
        if ($handle === false)
        {
           return false;
        }
        while (!feof($handle))
        {
            set_time_limit(0);
            $buffer = fread($handle, $chunksize);
            echo $buffer;
            @ob_flush();
            @flush();

        }
        $status = fclose($handle);
        return $status;
    }

    public function delete()
    {
        unlink($this->file_name);
    }

    public function getName()
    {
        return $this->file_name;
    }
}