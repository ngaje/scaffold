<?php
namespace Netshine\Scaffold;

/**
* @property-write int $profile_id
*/
class Language
{
    private $default_lang = 'en-GB';
    protected $language_folder = '';
    protected $current_lang = '';
    protected $profile_id = ''; //For custom overrides
    protected $language_resources = array();

    /**
    * @param string $language Language identifier (eg. en-US) - defaults to current language if none supplied
    * @return Language
    */
    public function __construct($language_folder, $language = '', $profile_id = '')
    {
        $this->language_folder = $language_folder;
        $this->setLanguage($language, $profile_id);
    }

    public function setLanguageFolder($language_folder)
    {
        $this->language_folder = $language_folder;
        $this->language_resources = array();
    }

    public function getLanguageFolder()
    {
        return $this->language_folder;
    }

    /**
    * Functional language arrays are lazy loaded
    * @param mixed $property
    */
    public function __get($property)
    {
        if (!array_key_exists($property, $this->language_resources)) {
            $this->language_resources[$property] = new LanguageResource($this, $property, $this->loadLanguageFile($property));
        }
        if (!$this->language_resources[$property]) {
            throw new Exception('Language file missing (' . $property . ')');
        }
        return $this->language_resources[$property];
    }

    public function __set($property, $value)
    {
        if ($property == 'profile_id') {
            if ($value &&
                    (file_exists($this->language_folder . '/custom_' . $value . '/' . $this->current_lang) &&
                    is_dir($this->language_folder . '/custom_' . $value . '/' . $this->current_lang))) {
                $this->profile_id = $value;
                return;
            }
        }
        else {
            throw new Exception('Property does not exist: ' . $property);
        }
    }

    /**
    * Switch to a different language
    * @param string $new_lang The new language string
    */
    public function setLanguage($language, $profile_id = '')
    {
        //Determine which language we are dealing with
        if (strlen($language) > 0) {
            if (!file_exists($this->language_folder . '/' . $language)
                    || !is_dir($this->language_folder . '/' . $language)) {
                $language = ''; //Language folder not available - use current or default
            }
        }
        if (!$language || !file_exists($this->language_folder . '/' . $language)) {
            $language = $this->default_lang;
        }
        $this->current_lang = $language;

        //If a profile ID has been passed in, only record it if we actually have any custom language files
        if ($profile_id &&
                    file_exists($this->language_folder . '/' . $language . '/' . $profile_id) &&
                    is_dir($this->language_folder . '/' . $language . '/' . $profile_id)) {
            $this->profile_id = $profile_id;
        }
    }

    /**
    * Combines the language file for the selected resource in the profile's custom language, with
    * the default resource language file for the current language and the default resource file for the
    * default language, with the profile and current language taking precedence, and any missing
    * entries being made up for by the defaults.
    * @param string $resource
    * @param boolean $using_profile For recursive use
    */
    protected function loadLanguageFile($resource, $using_profile = false)
    {
        $lang_ini = array();
        $default_ini = array();
        $profile_ini = array();

        if (!$using_profile && $this->profile_id) {
            $profile_ini = $this->loadLanguageFile($resource, true);
        }

        $file_name = $this->getFileName($this->current_lang, $resource, $using_profile);
        if ($file_name && file_exists($file_name))
        {
            $lang_ini = parse_ini_file($file_name);
            if (!$lang_ini) {
                $lang_ini = array(); //language file is not valid - use default instead
            }
        }
        $file_name = $this->getFileName($this->default_lang, $resource, $using_profile);
        if ($file_name && $this->current_lang != $this->default_lang && file_exists($file_name)) {
            $default_ini = parse_ini_file($file_name);
        }
        return $profile_ini + $lang_ini + $default_ini; //Any items missing from specified language file will be loaded from the default
    }

    /**
    * Returns the file name for the given resource in the given language. If a custom file exists for the object's profile
    * ID, that will take precedence over the default files.
    * @param string $language
    * @param string $resource
    * @param boolean $using_profile
    */
    protected function getFileName($language, $resource, $using_profile = false)
    {
        $file_name = false;
        if ($using_profile) {
            $file_name = $this->language_folder . '/custom_' . $this->profile_id . '/' . $language . '/' . $resource . '.ini';
        }
        if (!$file_name || !file_exists($file_name)) {
            $file_name = $this->language_folder . '/' . $language . '/' . $resource . '.ini';
        }

        //Try local language folder
        if (!file_exists($file_name)) {
            if ($using_profile) {
                $file_name = dirname(__FILE__) . '/Language/custom_' . $this->profile_id . '/' . $language . '/' . $resource . '.ini';
            }
            if (!$file_name || !file_exists($file_name)) {
                $file_name = dirname(__FILE__) . '/Language/' . $language . '/' . $resource . '.ini';
            }
        }
        return $file_name;
    }

    /**
    * Return a list of all the supported languages. This might be breaking the single responsibility
    * principle, but a separate class just to do this would be over-engineering (I reckon so anyway),
    * and we need this object's state anyway (profile ID)
    */
    public function getAllLanguageStrings()
    {
        $langs = array();
        $directory_contents = array_diff(scandir($this->language_folder), array(".", ".."));
        foreach ($directory_contents as $file_or_folder)
        {
            if (strlen($file_or_folder) > 0 && is_dir($this->language_folder . '/' . $file_or_folder)) {
                $langs[] = $file_or_folder;
            }
        }

        //Check for any custom files not already included
        if ($this->profile_id && is_dir($this->language_folder . '/custom_' . $this->profile_id)) {
            $directory_contents = array_diff(scandir($this->language_folder . '/custom_' . $this->profile_id), array(".", ".."));
            foreach ($directory_contents as $file_or_folder)
            {
                if (strlen($file_or_folder) > 0 && is_dir($this->language_folder . '/' . $file_or_folder) && array_search($file_or_folder, $langs) === false) {
                    $langs[] = $file_or_folder;
                }
            }
        }

        return $langs;
    }

    public function getCurrentLanguage()
    {
        return $this->current_lang;
    }
}