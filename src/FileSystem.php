<?php
namespace Netshine\Scaffold;

class FileSystem
{
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
    public function copyFiles($source, $target)
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
                mkdir($target . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            } else {
                copy($item, $target . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            }
            clearstatcache(true);
        }

        return true;
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
        if (is_dir($directory)) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($iterator as $item) {
                $item_path = $item->getRealPath();

                $skip = false;
                if ($allowed_directories) {
                    foreach ($allowed_directories as $allowed_directory) {
                        if (strpos($item_path, $allowed_directory) === 0) {
                            $skip = true;
                            break;
                        }
                    }
                }
                if (!$skip && (!$allowed_files || array_search($item->getRealPath(), $allowed_files) === false)) {
                    $do_delete = false;
                    if ($patterns && count($patterns)) {
                        foreach ($patterns as $pattern) {
                            if (preg_match($pattern, basename($item_path))) {
                                $do_delete = true;
                                break;
                            }
                        }
                    } else {
                        $do_delete = true;
                    }
                    if ($do_delete) {
                        if ($item->isDir()) {
                            rmdir($item_path);
                        } else {
                            unlink($item_path);
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