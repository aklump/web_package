<?php

namespace AKlump\WebPackage\Helpers;

use AKlump\WebPackage\Model\GitFlow;
use AKlump\WebPackage\Traits\HasConfigTrait;
use AKlump\WebPackage\Config\Config;

class GetBranchType {

  use HasConfigTrait;

  public function __construct($config) {
    $this->setConfig($config);
  }

  /**
   * @return int
   *
   * @see \AKlump\WebPackage\Model\GitFlow::FEATURE
   * @see \AKlump\WebPackage\Model\GitFlow::RELEASE
   * @see \AKlump\WebPackage\Model\GitFlow::HOTFIX
   */
  public function __invoke(string $branch_name): string {
    if (strstr($branch_name, 'release')) {
      return GitFlow::RELEASE;
    }
    elseif (strstr($branch_name, 'hotfix')) {
      return GitFlow::HOTFIX;
    }
    elseif ($branch_name === ($this->getConfig()[Config::MAIN_BRANCH] ?? GitFlow::MASTER)) {
      return GitFlow::MASTER;
    }
    elseif ($branch_name === ($this->getConfig()[Config::DEVELOP_BRANCH] ?? GitFlow::DEVELOP)) {
      return GitFlow::DEVELOP;
    }

    return GitFlow::FEATURE;
  }

}
