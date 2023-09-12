<?php

namespace AKlump\WebPackage\Traits;


trait HasConfigTrait {

  private $config;

  public function getConfig() {
    return $this->config;
  }

  public function setConfig($config) {
    $this->config = $config;
  }
}
