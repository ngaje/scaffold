<?php
namespace Netshine\Scaffold;

class FileSystem
{
    protected $files = array();

    /**
     * Copy single file from source to target
     * @param String $source
     * @param String $target
     */
    public function copyFile($source, $target)
    {
        if (is_dir($target)) {
            $target .= DIRECTORY_SEPARATOR . basename($source);
        }
        $result = copy($source, $target);
        clearstatcache(true);
        return $result;
    }

    /**
     * Recursively copy files from source to target
     * @param String $source
     * @param String $target
     */
    public function copyFiles($source, $target, $overwrite = false)
    {
        if (!is_dir($source)) {
            return false;
        } else if(!is_dir($target)) {
            if(!mkdir($target, null, true)) {
                return false;
            }
        }

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $item)
        {
            if ($item->isDir()) {
                if (!file_exists($target . DIRECTORY_SEPARATOR . $iterator->getSubPathName())) {
                    if (!@mkdir($target . DIRECTORY_SEPARATOR . $iterator->getSubPathName())) {
                        usleep(100000); //Wait a tenth of a second and try again
                        mkdir($target . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
                    }
                }
            } else {
                $do_copy = true;
                if (file_exists($target . DIRECTORY_SEPARATOR . $iterator->getSubPathName())) {
                    if ($overwrite) {
                        unlink($target . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
                    } else {
                        $do_copy = false;
                    }
                }
                if ($do_copy) {
                    copy($item, $target . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
                }
            }
            clearstatcache(true);
        }

        return true;
    }

    public function deleteDirectoryAndContents($directory)
    {
        if (file_exists($directory)) {
            $this->emptyDirectory($directory);
            if (count(glob($directory . "/*")) > 0) {
                clearstatcache();
                usleep(100000);
            }
            return rmdir($directory);
        }
    }

    /**
    * Recursively delete all files in a directory
    * @param string $directory
    * @param array $allowed_directories Fully qualified names of directories that should NOT be deleted
    * @param array $allowed_files Fully qualified names of files that should NOT be deleted
    * @param array $patterns Regex patterns to match - if omitted, all files will be deleted except those in the 'allowed' arrays, otherwise, only those matching the patterns supplied here will be deleted
    */
    public function emptyDirectory($directory, $allowed_directories = array(), $allowed_files = array(), $patterns = array())
    {
        $directory_strategy = function($directory_name) {if ($directory_name) {if (count(glob($directory_name . "/*")) > 0) {clearstatcache();usleep(100000);}rmdir($directory_name);}};
        $file_strategy = function($file_name) {if ($file_name) {unlink($file_name);}};
        return $this->iterateFiles($directory, $allowed_directories, $allowed_files, $patterns, $directory_strategy, $file_strategy);
    }

    /**
    * Recursively check every directory and every file within the given directory and return all the files (as File objects), optionally restricting to a particular regex pattern
    * @param string $directory
    * @param array $excluded_directories
    * @param array $excluded_files
    * @param array $patterns
    */
    public function findFiles($directory, $excluded_directories = array(), $excluded_files = array(), $patterns = array())
    {
        $this->files = array();
        $file_strategy = function($file_name) {$this->files[] = new File($file_name);};
        $this->iterateFiles($directory, $excluded_directories, $excluded_files, $patterns, null, $file_strategy);
        return $this->files;
    }

    /**
    * Recursively search every directory and file, returning those who match the given patterns, are not excluded, and which contain the search text
    * @param string $directory
    * @param array $searches Array of strings to search for
    * @param array $excluded_directories
    * @param array $excluded_files
    * @param array $patterns
    */
    public function findInFiles($directory, $searches, $excluded_directories = array(), $excluded_files = array(), $patterns = array())
    {
        $matches = array();
        $files = $this->findFiles($directory, $excluded_directories, $excluded_files, $patterns);
        foreach ($files as $file) {
            $contents = file_get_contents($file->getName());
            $contents_changed = false;
            foreach ($searches as $index=>$search) {
                if (strpos($contents, $search) !== false) {
                    $matches[] = $file;
                    break;
                }
            }
        }
        return $matches;
    }

    /**
    * Recursively search every directory and file, replacing the search text with the replace text wherever it is found
    * @param string $directory
    * @param array $searches Array of strings to search for (must have corresponding entry in $replaces array)
    * @param array $replaces Array of strings to replace with (indexes must match $searches array)
    * @param array $excluded_directories
    * @param array $excluded_files
    * @param array $patterns
    */
    public function replaceInFiles($directory, $searches, $replaces, $excluded_directories = array(), $excluded_files = array(), $patterns = array())
    {
        $files = $this->findFiles($directory, $excluded_directories, $excluded_files, $patterns);
        foreach ($files as $file) {
            $contents = file_get_contents($file->getName());
            $contents_changed = false;
            foreach ($searches as $index=>$search) {
                if (strpos($contents, $search) !== false) {
                    $contents = str_replace($search, $replaces[$index], $contents);
                    $contents_changed = true;
                }
            }
            if ($contents_changed) {
                file_put_contents($file->getName(), $contents);
            }
        }
    }

    /**
    * Perform some action (specified using a closure in the $strategy parameter) on every matching file in the given directory
    * @param string $directory
    * @param array $excluded_directories Fully qualified names of directories that should NOT be acted on
    * @param array $excluded_files Fully qualified names of files that should NOT be acted on
    * @param array $patterns Regex patterns to match - if omitted, all files will be acted on except those in the 'excluded' arrays, otherwise, only those matching the patterns supplied here will be acted on
    * @param \Closure $directory_strategy Action to take on matching directories - it takes a single parameter which is the fully qualified directory name
    * @param \Closure $file_strategy Action to take on matching files - it takes a single parameter which is the fully qualified file name
    */
    protected function iterateFiles($directory, $excluded_directories = array(), $excluded_files = array(), $patterns = array(), \Closure $directory_strategy = null, \Closure $file_strategy = null)
    {
        if (is_dir($directory)) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($iterator as $item) {
                $item_path = $item->getRealPath();

                $skip = false;
                if ($excluded_directories) {
                    foreach ($excluded_directories as $excluded_directory) {
                        if (@strpos($item_path, $excluded_directory) === 0) {
                            $skip = true;
                            break;
                        }
                    }
                }
                if (!$skip && (!$excluded_files || array_search($item->getRealPath(), $excluded_files) === false)) {
                    $do_action = false;
                    if ($patterns && count($patterns)) {
                        foreach ($patterns as $pattern) {
                            if (preg_match($pattern, basename($item_path))) {
                                $do_action = true;
                                break;
                            }
                        }
                    } else {
                        $do_action = true;
                    }
                    if ($do_action) {
                        if ($item->isDir()) {
                            if ($directory_strategy !== null) {
                                $directory_strategy($item_path);
                            }
                        } else {
                            if ($file_strategy !== null) {
                                $file_strategy($item_path);
                            }
                        }
                        clearstatcache(true);
                    }
                }
            }
            return true;
        }
        return false;
    }
}