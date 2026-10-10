<?php

namespace AKlump\WebPackage\Helpers;

use AKlump\WebPackage\Config\ConfigDefaults;
use Jawira\CaseConverter\Convert;
use Symfony\Component\Filesystem\Path;

class GetDerivativeComposerName {

  public function __invoke() {
    $composer_json = json_decode(file_get_contents((new GetWebPackageRoot())() . '/composer.json'), TRUE);
    $derivative_composer_name = explode('/', $composer_json['name'], 2)[1] . '/';
    $install_dir = ConfigDefaults::INSTALL_DIR;
    if (!Path::isAbsolute($install_dir)) {
      $install_dir = getcwd() . "/$install_dir";
    }
    $project = basename(realpath(dirname($install_dir)));
    $project = (new Convert($project))->toKebab();
    $derivative_composer_name .= $project;

    return $derivative_composer_name;
  }
}
