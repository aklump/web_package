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
abstract class ConfigFileBasedStorage extends Config {

  /**
   * Constructor
   *
   * @param string $dir      Directory where the config will be stored.  You
   *                         may also pass the full path to an existing file,
   *                         in which case the dirname will be set as $dir and
   *                         the basename as $basename automatically for
   *                         you--$basename must be null in this case.
   * @param string $basename The config file basename.  Optional
   * @param array  $options  Defaults to expanded.
   *                         - install boolean Set this to true and $dir will
   *                         be created (and config file) if it doesn't already
   *                         exist.
   *                         - encode @see json_encode.options
   */
  public function __construct($dir, $basename = NULL, $options = array()) {

    if (is_null($basename) && is_file($dir)) {
      $dir = dirname($dir);
      $basename = basename($dir);
    }

    $basename = isset($basename) ? $basename : 'config.' . static::EXTENSION;

    if (empty($dir)) {
      throw new \InvalidArgumentException("First argument: dir, may not be empty.");
    }
    if (!is_string($basename) || strpos($basename, '/')) {
      throw new \InvalidArgumentException("Second argument must be the basename for a file.");
    }

    $this->getStorage()->type = 'file';
    $this->getStorage()->value = $dir . '/' . $basename;
    $this->options = $options + $this->defaultOptions;

    // Do we want to install the directory?
    $install = !empty($options['install']);
    if ($install) {
      $this->init();
    }

    if (!is_dir($dir)) {
      throw new \InvalidArgumentException("First argument must be an existing directory. Consider using the 'install' option.");
    }

    parent::__construct();
  }

  protected function init_file() {
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

  protected function _read() {
    return file_get_contents($this->getStorage()->value);
  }

  protected function _write($data) {
    return file_put_contents($this->getStorage()->value, $data) !== FALSE;
  }
}
