<?php

namespace AKlump\WebPackage\Config;

use AKlump\WebPackage\Helpers\GetRootPath;
use AKlump\WebPackage\Traits\HasConfigTrait;
use AKlump\WebPackage\VersionScribeFactory;
use AKlump\WebPackage\VersionScribeInterface;
use Symfony\Component\Filesystem\Path;

class GetVersionScribe {

  use HasConfigTrait;

  public function __construct($config) {
    $this->setConfig($config);
  }

  public function __invoke(): VersionScribeInterface {
    $factory = new VersionScribeFactory();
    $version_file = $this->getConfig()['info_file'];
    $version_file = Path::makeAbsolute($version_file, (new GetRootPath())());

    return $factory($version_file);
  }

}
