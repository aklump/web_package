<?php
/**
 * @file
 * Defines the base class for all file-based storage configurations.
 */
namespace AKlump\LoftLib\Component\Config;

/**
 * Represents a ConfigJson object class.
 *
 * @brief Handles configuration in a Json file.
 */
abstract class ConfigFileBasedStorage extends Config
{

    protected $options = array();

    /**
     * Constructor
     *
     * @param string $dir      Directory where the config will be stored.  You
     *                         may also pass the full path to an existing file,
     *                         in which case the dirname will be set as $dir
     *                         and
     *                         the basename as $basename automatically for
     *                         you--$basename must be null in this case.
     * @param string $basename The config file basename.  Optional
     * @param array  $options  Defaults to expanded.
     *                         - install boolean Set this to true and $dir will
     *                         be created (and config file) if it doesn't
     *                         already exist.
     *                         - encode @see json_encode.options
     */
    public function __construct($dir, $basename = null, $options = array())
    {
        $this->options = $options + $this->defaultOptions();

        if (is_null($basename) && is_file($dir)) {
            $basename = basename($dir);
            $dir = dirname($dir);
        }

        if (empty($dir)) {
            throw new \InvalidArgumentException("First argument: dir, may not be empty.");
        }
        if (!is_null($basename) && !is_string($basename)) {
            throw new \InvalidArgumentException("Basename must be a string.");
        }

        $basename = isset($basename) ? $basename : 'config';
        // Assure we have the file extension, preserving .yml
        if ($this->options['auto_extension'] && !preg_match('/\.yml|' . static::EXTENSION . '$/', $basename)) {
            $basename .= '.' . static::EXTENSION;
        }

        if (strpos($basename, '/')) {
            throw new \InvalidArgumentException("Second argument must be the basename for a file.");
        }

        $this->getStorage()->type = 'file';
        $this->getStorage()->value = $dir . '/' . $basename;


        // Do we want to install the directory?
        $install = !empty($this->options['install']);
        if ($install) {
            $this->init();
        }

        if (!is_dir($dir)) {
            throw new \InvalidArgumentException("First argument must be an existing directory. Consider using the 'install' option.");
        }

        parent::__construct();
    }

    public function defaultOptions()
    {
        return array(
            'auto_extension' => true,
            'eof_eol'        => true,
        ) + parent::defaultOptions();
    }

    protected function init_file()
    {
        $path = $this->getStorage()->value;
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        if (!file_exists($path)) {
            touch($path);
        }
        if (!is_readable($path)) {
            throw new \RuntimeException("Could not initialize $path for storage.");
        }
    }

    protected function _read()
    {
        return file_get_contents($this->getStorage()->value);
    }

    protected function _write($data)
    {
        $data = $this->getFileHeader() . $data;
        $data = $this->getFileFooter() . $data;
        if ($this->options['eof_eol']) {
            $data = rtrim($data, PHP_EOL) . PHP_EOL;
        }

        return file_put_contents($this->getStorage()->value, $data) !== false;
    }

    protected function getFileHeader()
    {
        return '';
    }

    protected function getFileFooter()
    {
        return '';
    }
}
