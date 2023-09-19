<?php

namespace AKlump\WebPackage\Helpers;

use Symfony\Component\Filesystem\Filesystem;
use Webmozart\Glob\Glob;

class GetAllTemplates {

  const DEFAULT = 'USER_DEFAULT';

  /**
   * Get all available templates.
   *
   * @return array
   *   Keys are template names. Values are the absolute paths to the templates.
   */
  public function __invoke(): array {
    $template_dir = (new GetServerHome())() . '/.web_package';
    $filesystem = new Filesystem();
    if (!$filesystem->exists($template_dir)) {
      return [];
    }
    $paths = Glob::glob("$template_dir/config*");
    $labels = array_map(function (string $path) {
      if (preg_match('/^config_?(.*)/i', basename($path), $matches)) {
        return $matches[1] ?: self::DEFAULT;
      }
    }, $paths);

    return array_combine($labels, $paths);
  }

}
