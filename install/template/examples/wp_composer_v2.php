<?php
/**
 * Updates a composer.json file with web_package info.
 */
use Symfony\Component\Yaml\Yaml;
use \AKlump\LoftLib\Component\Config\ConfigYaml;
use \AKlump\LoftLib\Component\Config\ConfigJson;

// Leveraging web_package's dependencies.
require_once $argv[12] . '/vendor/autoload.php';

// Disable this option for compressed json files.
$options = array('encode' => JSON_PRETTY_PRINT);
$json = new ConfigJson($argv[7] . '/composer.json', NULL, $options);
$json->write('description', $argv[4]);

// https://getcomposer.org/doc/04-schema.md#version
$json->write('version', $argv[2]);
