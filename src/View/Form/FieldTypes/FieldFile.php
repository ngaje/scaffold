<?php
namespace Netshine\Scaffold\View\Form\FieldTypes;

use Netshine\Scaffold\View\Form\FieldBase;
use Netshine\Scaffold\Request;

class FieldFile extends FieldBase
{
    public $max_upload_size = 2048; //2MB
    public $allowed_types = array('jpg', 'gif', 'png', 'bmp', 'txt', 'pdf', 'odt', 'ods', 'doc', 'csv', 'xls');
    public $orig_file_names = array();

    protected $staging_folder;
    protected $upload_folder;

    /**
    * Specify the folders to save file uploads to. Folders must exist, be a directory, and be writable (otherwise renderer will not show the field).
    * @param string $staging_folder Temporary location to store files during form processing
    * @param string $upload_folder Final destination folder after successful form submission
    * @return boolean Whether or not the folders were set successfully
    */
    public function setUploadFolders($staging_folder, $upload_folder)
    {
        if (file_exists($staging_folder) && is_dir($staging_folder) && is_writable($staging_folder)) {
            $this->staging_folder = $staging_folder;
            if (file_exists($upload_folder) && is_dir($upload_folder) && is_writable($upload_folder)) {
                $this->upload_folder = $upload_folder;
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getStagingFolder()
    {
        return isset($this->staging_folder) ? $this->staging_folder : false;
    }

    public function getUploadFolder()
    {
        return isset($this->upload_folder) ? $this->upload_folder : false;
    }

    public function validate(Request $request, &$message = null, $suppress_errors = false)
    {
        if (parent::validate($request, $message, $suppress_errors)) {
            $valid = true;
            if (strlen(@$_FILES[$this->name]['name']) > 0)
            {
                if (count($_FILES) > 0)
                {
                    $file_info = $_FILES[$this->name];
                    $valid = $this->checkFileSize($file_info);
                    $valid = $valid ? $this->checkFileType($file_info) : $valid;
                    $valid = $valid ? $this->checkPhpErrors($file_info) : $valid;
                }
            }
            return $valid;
        } else {
            return false;
        }
    }

    protected function checkFileSize($file_info)
    {
        if ($file_info['size'] > intval($this->max_upload_size) * 1024)
        {
            $this->error = sprintf($this->language->form['err_fld_file_too_big'], $this->max_upload_size / 1024);
            return false;
        }
        return true;
    }

    protected function checkFileType($file_info)
    {
        if ($file_info['size'] > 0)
        {
            //Check file extension (no point checking mime type, as many browsers don't bother checking)
            if (array_search(pathinfo($file_info['name'], PATHINFO_EXTENSION), $this->allowed_types) === false)
            {
                $this->error = sprintf($this->language->form['err_fld_file_invalid_file_type'], implode("&nbsp; " , $this->allowed_types));
                return false;
            }
        }
        return true;
    }

    protected function checkPhpErrors($file_info)
    {
        if ($file_info['error'])
        {
            $error_reason = "";
            switch ($file_info['error'])
            {
                case UPLOAD_ERR_INI_SIZE:
                    $error_reason = 'err_fld_file_ini_size';
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $error_reason = 'err_fld_file_form_size';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $error_reason = 'err_fld_file_partial';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $error_reason = 'err_fld_file_no_file';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $error_reason = 'err_fld_file_no_tmp_dir';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $error_reason = 'err_fld_file_cant_write';
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $error_reason = 'err_fld_file_extension';
                    break;
            }
            if (strlen($error_reason) > 0)
            {
                $this->error = sprintf($this->language->form['err_fld_file_upload_failed_reason'], $this->language->form[$error_reason]);
            }
            else
            {
                $this->error = $this->language->form['err_fld_file_upload_failed'];
            }
            return false;
        }
        return true;
    }

    /**
    * Copy uploaded files to the staging area
    * @param string $message
    * @return boolean
    */
    public function process(Request $request, &$message)
    {
        $this->clearDownOldTempFiles();
        clearstatcache();

        if ($request->getRequestParam('delete_' . $this->name)) {
            //Delete file from upload or staging area
            $this->deleteFile();
            $message = $this->language->form['fld_file_deleted'];
            return false;
        } else {
            $this->uploadToStaging();
        }
        return true;
    }

    protected function deleteFile()
    {
        $file_name = $this->getValue();
        $this->orig_file_names[] = $file_name;
        if (strlen($file_name) > 0) {
            $full_file_name = $this->staging_folder . '/tmp_' . $this->parent_field_set->parent_form->submission_id . '_' . $file_name;
            if (file_exists($full_file_name)) {
                unlink($full_file_name);
            }
        }
        $this->setValue(null);
    }

    protected function uploadToStaging()
    {
        if (count($_FILES) > 0)
        {
            foreach ($_FILES as $key=>$file_info)
            {
                if ($key == $this->name && $file_info['size'] > 0)
                {
                    $final_file_name = strtolower(str_replace(" ", "-", $file_info['name']));
                    $final_file_name = preg_replace("/[^\_\-\.a-zA-Z0-9]/", "", $final_file_name);
                    $final_file_name = $this->upload_folder . '/' . $final_file_name;

                    $loop_counter = 1;
                    $path_parts = pathinfo($final_file_name);
                    $extension = "." . $path_parts['extension'];
                    $root_final_file_name = $this->upload_folder . '/' . $path_parts['filename'];

                    while (file_exists($final_file_name))
                    {
                        $loop_counter++;
                        if ($loop_counter > 100)
                        {
                            $message = sprintf($this->language->form['err_fld_file_upload_failed_reason'], $this->language->form['err_fld_file_exists']);
                            return false;
                        }
                        $final_file_name = $root_final_file_name . "_$loop_counter" . "$extension";
                    }

                    if (!move_uploaded_file($file_info['tmp_name'], $this->staging_folder . '/tmp_' . $this->parent_field_set->parent_form->submission_id . '_' . basename($final_file_name)))
                    {
                        $message = sprintf($this->language->form['err_fld_file_upload_failed_reason'], $this->language->form['err_fld_file_copy_failed']);
                        return false;
                    }

                    $this->setValue(basename($final_file_name));
                }
            }
        }
    }

    public function setValue($value = '')
    {
        $tmp_file_name = isset($this->parent_field_set->parent_form->submission_id) ? $this->staging_folder . '/tmp_' . $this->parent_field_set->parent_form->submission_id . '_' . $value : null;
        $final_file_name = $this->upload_folder . '/' . $value;
        if (($tmp_file_name && file_exists($this->staging_folder . '/tmp_' . $this->parent_field_set->parent_form->submission_id . '_' . $value))
                || ($final_file_name && file_exists($this->upload_folder . '/' . $value))) {
            $this->value = $value;
        } else {
            $this->value = '';
        }
    }

    /**
    * Move file from staging area to final destination
    * @param string $message
    * @return boolean
    */
    public function formSubmitted(Request $request, &$message)
    {
        $success = true;
        clearstatcache();
        $value = $this->getValue();
        if (isset($value) && strlen($value) > 0) {
            $tmp_file = $this->staging_folder . '/tmp_' . $this->parent_field_set->parent_form->submission_id . '_' . $value;
            if (file_exists($tmp_file) && file_exists($this->upload_folder . '/' . $value)) {
                $success = false;
                $message = $this->language->form['err_fld_file_exists'];
            } else {
                if (file_exists($tmp_file)) {
                    $success = rename($tmp_file, $this->upload_folder . '/' . $value);
                    if (!$success) {
                        $message = $this->language->form['err_fld_file_copy_from_staging_failed'];
                    }
                }
            }
        }

        //If original file has been deleted, remove it from the file system
        $orig_file_names = explode(",", $request->getRequestParam('orig_' . $this->name));
        foreach ($orig_file_names as $orig_file_name)
        {
            if (strlen($orig_file_name) > 0 && $this->value != $orig_file_name && strpos($orig_file_name, '..') === false) {
                $full_file_name = $this->upload_folder . '/' . filter_var($orig_file_name, FILTER_SANITIZE_STRING);
                if (file_exists($full_file_name)) {
                    unlink($full_file_name);
                }
            }
        }

        $this->clearDownOldTempFiles();

        return $success;
    }

    protected function clearDownOldTempFiles()
    {
        clearstatcache();
        $files = array_diff(scandir($this->staging_folder), array('.', '..'));
        foreach ($files as $file) {
            if (filemtime($this->staging_folder . '/' . $file) < time() - 86400) {
                @unlink($this->staging_folder . '/' . $file);
            }
        }
    }
}