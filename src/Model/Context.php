<?php

namespace AKlump\WebPackage\Model;

use AKlump\WebPackage\Config\LoadConfig;
use AKlump\WebPackage\Helpers\GetBranchType;
use AKlump\WebPackage\Helpers\GetCurrentBranch;
use AKlump\WebPackage\Helpers\GetServerHome;

class Context implements ContextInterface {

  /**
   * @var \AKlump\WebPackage\Config\LoadConfig
   */
  private $configLoader;

  public function __construct(LoadConfig $loader) {
    $this->configLoader = $loader;
  }

  public function getRootPath(): string {
    return ROOT_PATH;
  }

  public function getServerHome(): string {
    return (new GetServerHome())();
  }

  public function getCurrentBranch(): string {
    return (new GetCurrentBranch())();
  }

  public function getBranchType(string $branch_name): string {
    if (empty($branch_name)) {
      return '';
    }

    return (new GetBranchType($this->getConfig()))($branch_name);
  }

  public function getConfig() {
    if (empty($this->config)) {
      $this->config = ($this->configLoader)($this->getRootPath());
    }

    return $this->config;
  }
}
