<?php

namespace AKlump\WebPackage\Validator\Constraint;

use AKlump\WebPackage\Helpers\GetCurrentBranch;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class GitBranchValidator extends ConstraintValidator {

  use \AKlump\WebPackage\Traits\ShellCommandTrait;

  /**
   * @inheritDoc
   */
  public function validate($value, Constraint $constraint) {
    if (!$constraint instanceof GitBranch && !$constraint instanceof NotGitBranch) {
      throw new UnexpectedTypeException($constraint, GitBranch::class . '|' . NotGitBranch::class);
    }

    if (get_class($constraint) === GitBranch::class && in_array($value, $constraint->options)) {
      return;
    }

    if (get_class($constraint) === NotGitBranch::class && !in_array($value, $constraint->options)) {
      return;
    }

    if ('' === $value) {
      $this->context->addViolation($constraint->messageNotInitialized);
    }
    else {
      $branch = (new GetCurrentBranch())();
      try {
        $commits = (int) $this->exec(sprintf('git rev-list %s --count 2>/dev/null', $branch));
      }
      catch (\Exception $exception) {
        $commits = 0;
      }
      if (0 === $commits) {
        $this->context->addViolation($constraint->messageNoCommits);
      }
      elseif (!empty($constraint->options)) {
        $this->context->addViolation($constraint->messageInvalidOption);
      }
    }
  }

}
