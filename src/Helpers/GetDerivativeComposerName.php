<?php

namespace AKlump\WebPackage\Helpers;

use Symfony\Component\Filesystem\Path;

class GetDerivativeComposerName {

  public function __invoke() {
    $composer_json = json_decode(file_get_contents(WEB_PACKAGE_ROOT . '/composer.json'), TRUE);
    $derivative_composer_name = explode('/', $composer_json['name'], 2)[1] . '/';
    $foo = INSTALL_DIR;
    if (!Path::isAbsolute($foo)) {
      $foo = getcwd() . "/$foo";
    }
    $derivative_composer_name .= basename(realpath(dirname($foo)));

    return $derivative_composer_name;
  }
}
