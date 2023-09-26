<?php

namespace AKlump\WebPackage\Model;

interface ContextInterface {

  public function getCurrentBranch(): string;

  public function getBranchType(string $branch_name): string;

  public function getConfig();
}
