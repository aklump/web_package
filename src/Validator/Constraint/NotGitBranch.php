<?php

namespace AKlump\WebPackage\Validator\Constraint;

class NotGitBranch extends GitBranch {

  /**
   * To meet this constraint, $value must NOT be one of these.
   *
   * @var array
   *
   */
  public $options = [];

  public function validatedBy() {
    return GitBranchValidator::class;
  }

}
