<?php

namespace AKlump\WebPackage\Config;

use AKlump\WebPackage\VersionScribeFactory;
use AKlump\WebPackage\VersionScribeInterface;

class GetVersionScribe {

  public function __invoke(): VersionScribeInterface {
    $config = (new LoadConfig())();
    $factory = new VersionScribeFactory();

    return $factory($config['info_file']);
  }

}
