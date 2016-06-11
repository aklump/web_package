<?php
/**
 * @file
 * Use this class to use JSON based configuration files.
 */
namespace AKlump\LoftLib\Component\Config;

/**
 * Represents a ConfigJson object class.
 *
 * @brief Handles configuration in a Json file.
 *
 * $options for __construct()
 *   - encode @see json_encode.options
 */
class ConfigJson extends ConfigFileBasedStorage {

  const EXTENSION = "json";

  protected $defaultOptions = array(
    'encode' => NULL,
  );

  public function _read() {
    $data = parent::_read();

    return $data ? json_decode($data, TRUE) : array();
  }

  public function _write($data) {
    $options = NULL;
    if (isset($this->options['encode'])) {
      $options = $this->options['encode'];
    }
    elseif (defined('JSON_PRETTY_PRINT')) {
      $options = JSON_PRETTY_PRINT;
    }
    $data = json_encode($data, $options);

    return parent::_write($data);
  }
}
