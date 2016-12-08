<?php
/**
 * @file
 * Use this class to use YAML based configuration files.
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
        return array() + parent::defaultOptions();
    }

    public function _read()
    {
        return parse_ini_file($this->getStorage()->value);
    }

    public function _write($data)
    {
        foreach ($data as $key => $elem) {
            if (is_array($elem)) {
                for ($i = 0; $i < count($elem); $i++) {
                    $content[] = $key . "[] = \"" . $elem[$i] . "\"";
                }
            }
            else {
                if ($elem == "") {
                    $content[] = $key . " = ";
                }
                else {
                    $content[] = $key . " = \"" . $elem . "\"";
                }
            }
        }
        if ($content) {
            $content = implode(PHP_EOL, $content);

            return parent::_write($content);
        }
    }
}
