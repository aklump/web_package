<?php
/**
 * @file
 * Use this class to use INI based configuration files.
 */
namespace AKlump\LoftLib\Component\Config;

/**
 * Represents a ConfigIni object class.
 *
 * @brief Handles configuration in a ini file
 *
 * $options for __construct()
 *
 * @see   parse_ini_file().
 */
class ConfigIni extends ConfigFileBasedStorage
{

    const EXTENSION = "ini";

    public function defaultOptions()
    {
        return array(
                'process_sections' => false,
            ) + parent::defaultOptions();
    }

    public function _read()
    {
        $data = file_get_contents($this->getStorage()->value);
        $this->preserveComments($data);

        return parse_ini_string($data, $this->options['process_sections']);
    }

    public function _write($data)
    {
        $content = array();
        foreach ($data as $key => $elem) {
            if (is_array($elem)) {
                $content = array_merge($content, $this->arrayHandler($elem, $key));
            }
            else {
                if ($elem == "") {
                    $content[] = $key . " = ";
                }
                else {
                    $content[] = $key . " = \"" . trim($elem) . "\"";
                }
            }
        }
        $content = array_merge($content, $this->cache->comments);
        if ($content) {
            $content = implode(PHP_EOL, $content);

            return parent::_write($content);
        }
    }

    /**
     * Flattens arrays per the Drupal .info spec.
     *
     * @param array  $array
     * @param string &$parent Initially the key that represents $array.
     * @param string &$result Internal use only.
     *
     * @return string
     *
     *
     * // TODO This does not handle multi-dimensional arrays correctly. It
     * needs to use headers.  When we refactor this, this verion needs to move
     * to the drupal info class, as it works correctly for drupal.
     */
    protected function arrayHandler(array $array, &$parent = '', &$result = '')
    {
        foreach (array_keys($array) as $key) {
            $base = $parent;
            $parent .= is_numeric($key) ? '[]' : "[$key]";
            if (is_array($array[$key])) {
                $this->arrayHandler($array[$key], $parent, $result);
            }
            else {
                $result .= $parent . ' = "' . trim($array[$key]) . '"' . PHP_EOL;
                $parent = $base;
            }
        }

        return explode(PHP_EOL, $result);
    }

    protected function preserveComments($file_contents)
    {
        preg_match_all('/^\s*;.+$/m', $file_contents, $matches);
        $this->cache->comments = $matches ? $matches[0] : array();
    }
}
