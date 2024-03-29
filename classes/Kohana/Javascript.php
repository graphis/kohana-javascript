<?php defined('SYSPATH') or die('No direct script access.');
/*
 * @package		Javascript
 * @author      Pap Tamas
 * @copyright   (c) 2011-2012 Pap Tamas
 * @website		https://github.com/paptamas/kohana-javascript
 * @license		http://www.opensource.org/licenses/isc-license.txt
 *
 */

class Kohana_Javascript {

    // Directories to scan
    protected $_dirs;

    // Files found in directories
    protected $_files = array();

    // Script created from all js files
    protected $_script;

    // Array containing config items
    protected $_config;

    // Singleton instance
    protected static $_instance;

    /**
     * Construct
     *
     * @param $config_group
     */
    protected function __construct($config_group) {
        $this->_config = Kohana::$config
            ->load($config_group);
    }

    /**
     * Scan all specified directories for js files
     *
     * @return Kohana_Javascript
     */
    public function scan()
    {
        // Init file list
        $this->_files = array();

        // Get external resources
        $external = $this->_config->get('external');

        // Add external resources to file list
        foreach ($external as $resource)
        {
            $this->_add_file($resource);
        }

        // Get resources with higher priority
        $priority = $this->_config->get('priority');

        // Add resources with priority to file list
        foreach ($priority as $resource)
        {
            $this->_add_file($resource);
        }

        // Get directory list to scan
        $this->_dirs = $this->_config->get('dirs');

        // Scan each directory
        foreach ($this->_dirs as $dir)
        {
            $this->_do_scan($dir);
        }

        return $this;
    }

    /**
     * Recursively scan a directory
     *
     * @param $dir
     */
    protected function _do_scan($dir)
    {
        // Exclude directory if it is on exclude list
        $exclude = $this->_config->get('exclude');
        if (in_array($dir, $exclude))
        {
            return FALSE;
        }


        // List of directories found
        $dirs = array();

        $files = scandir($dir);
        foreach ($files as $file) {
            if (($file != '.') AND ($file != '..') AND (is_dir($dir.'/'.$file)))
            {
               $dirs[] = $dir.'/'.$file;
            }
            elseif ($this->_filter($dir, $file))
            {
                $this->_add_file($dir.'/'.$file);
            }
        }

        // Scan all directories found
        foreach ($dirs as $dir)
        {
            $this->_do_scan($dir);
        }
    }

    /**
     * Filter scanned files
     *
     * @param $dir
     * @param $file
     * @return bool
     */
    protected function _filter($dir, $file)
    {
        // Exclude '.' and '..'
        if (($file == '.') OR ($file == '..'))
            return FALSE;

        // Only add js files
        if (pathinfo($file, PATHINFO_EXTENSION) != 'js')
        {
            return FALSE;
        }

        // Exclude file if already in list
        if (in_array($dir.'/'.$file, $this->_files))
        {
            return FALSE;
        }

        // Exclude file if it is on exclude list
        $exclude = $this->_config->get('exclude');
        if (in_array($dir.'/'.$file, $exclude))
        {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Add file to file list if not already added, considering dependencies
     *
     * @param $file
     * @return bool
     */
    protected function _add_file($file)
    {
        // If already added, return
        if (in_array($file, $this->_files))
        {
            return FALSE;
        }

        // Get dependency list
        $dependencies = $this->_config->get('dependencies');

        // Get dependencies of current file
        $d = Arr::get($dependencies, $file, array());

        // Consider dependencies (first add files the current files depends on)
        foreach ($d as $d_file)
        {
            $this->_add_file($d_file);
        }

        // Add current file to file list
        $this->_files[] = $file;
    }

    /**
     * Return list of files
     *
     * @return array
     */
    public function files()
    {
        return $this->files();
    }

    /**
     * Combine all javascript resources
     *
     * @param $include_external
     * @return Kohana_Javascript
     */
    public function combine($include_external = FALSE)
    {
        // This can take a while
        set_time_limit(0);

        // Get external resource list
        $external = $this->_config->get('external');

        foreach ($this->_files as $file)
        {
            if (( ! in_array($file, $external)) OR ($include_external))
            {
                if (in_array($file, $external))
                {
                    $this->load_external($file);
                }
                else
                {
                    $this->load_file($file);
                }
                $this->_script .= PHP_EOL.PHP_EOL;
            }
        }

        return $this;
    }

    /**
     * Load javascript file into combined script
     *
     * @param $file
     */
    protected function load_file($file)
    {
        $handle = fopen($file, 'r');
        while ($data = fread($handle, 2048))
        {
            $this->_script .= $data;
        }
    }

    /**
     * Load external javascript file into combined script
     *
     * @param $url
     */
    protected function load_external($url)
    {
        $handle = curl_init();
        $timeout = 5;
        curl_setopt($handle, CURLOPT_URL, $url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($handle);
        curl_close($handle);
        $this->_script .= $data;
    }

    /**
     * Compresses the combined script
     *
     * @throws Kohana_Exception
     * @return Kohana_Javascript
     */
    public function compress()
    {
        if ( ! $this->_script)
        {
            throw new Kohana_Exception('First combine the scripts, and then compress.');
        }

        // Include jsmin
        require Kohana::find_file('vendor/jsmin', 'jsmin');
        $this->_script = JSMin::minify($this->_script);

        return $this;
    }

    /**
     * Render as one, combined script (inline), or as resource list
     *
     * @return Kohana_Javascript
     */
    public function render()
    {
        $r = '';

        if ($this->_script)
        {
            // The scripts were combined
            $r .=  '<script type="text/javascript">'.PHP_EOL;
            $r .=  $this->_script.PHP_EOL;
            $r .=  '</script>'.PHP_EOL;
        }
        else
        {
            // Get base url
            $base_url = rtrim($this->_config->get('base_url'), '/');

            // Get external resource list
            $external = $this->_config->get('external');

            // The script weren't combined
            foreach ($this->_files as $file)
            {

                if ( ! in_array($file, $external))
                {
                    $file = $base_url.'/'.$file;
                }

                $r .= HTML::script($file).PHP_EOL;
            }
        }

        return $r;
    }

    /**
     * Save the combined script to file
     *
     * @param $file
     * @throws Kohana_Exception
     * @return Kohana_Javascript
     */
    public function save($file)
    {
       if ( ! $this->_script)
       {
           throw new Kohana_Exception('First combine the scripts, and then save.');
       }

        file_put_contents($file, $this->_script);

        return $this;
    }

    /**
     * Returns a singleton instance of the class.
     *
     * @param $config_group
     * @return Javascript
     */
    public static function instance($config_group)
    {
        if ( ! Javascript::$_instance instanceof Javascript)
        {
            Javascript::$_instance = new Javascript($config_group);
        }

        return Javascript::$_instance;
    }
}

// END Kohana_Javascript