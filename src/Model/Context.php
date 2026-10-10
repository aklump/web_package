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

  private $config;

  /**
   * @var string
   */
  private $rootPath;

  /**
   * @param \AKlump\WebPackage\Config\LoadConfig $loader
   * @param string $root_path
   *   The absolute path of the project, i.e., the directory containing
   *   .web_package.
   */
  public function __construct(LoadConfig $loader, string $root_path) {
    $this->configLoader = $loader;
    $this->rootPath = $root_path;
  }

  public function getRootPath(): string {
    return $this->rootPath;
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
      $this->config = ($this->configLoader)();
    }

    return $this->config;
  }
}
