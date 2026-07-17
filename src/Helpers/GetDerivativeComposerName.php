<?php

namespace AKlump\WebPackage\Helpers;

use Jawira\CaseConverter\CaseConverter;
use Symfony\Component\Filesystem\Path;

class GetDerivativeComposerName {

  public function __invoke() {
    $composer_json = json_decode(file_get_contents(WEB_PACKAGE_ROOT . '/composer.json'), TRUE);
    $derivative_composer_name = explode('/', $composer_json['name'], 2)[1] . '/';
    $foo = INSTALL_DIR;
    if (!Path::isAbsolute($foo)) {
      $foo = getcwd() . "/$foo";
    }
    $project = basename(realpath(dirname($foo)));
    $project = (new CaseConverter())->convert($project)->toKebab();
    $derivative_composer_name .= $project;

    return $derivative_composer_name;
  }
}
