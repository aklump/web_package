<?php

namespace AKlump\WebPackage\Helpers;

use AKlump\WebPackage\Config\Config;
use AKlump\WebPackage\Config\ConfigDefaults;
use AKlump\WebPackage\Traits\HasConfigTrait;
use AKlump\WebPackage\VersionScribeInterface;

class GetCurrentVersion {

  use HasConfigTrait;

  /** @var \AKlump\WebPackage\VersionScribeInterface */
  protected $scribe;

  public function __construct($config, VersionScribeInterface $scribe) {
    $this->setConfig($config);
    $this->scribe = $scribe;
  }

  public function __invoke(): string {
    $version = $this->scribe->read();
    if (empty($version)) {
      $version = $this->getConfig()[Config::INITIAL_VERSION] ?? ConfigDefaults::INITIAL_VERSION;
    }

    return $version;
  }

}
