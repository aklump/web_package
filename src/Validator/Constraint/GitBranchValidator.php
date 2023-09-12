<?php

namespace AKlump\WebPackage\Validator\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class GitBranchValidator extends ConstraintValidator {

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

    $this->context->addViolation($constraint->messageInvalidOption);

    if ('' === $value) {
      $this->context->addViolation($constraint->messageNotInitialized);
    }

    $commits = (int) shell_exec(sprintf('git rev-list --count %s 2>/dev/null', $value));
    if (0 === $commits) {
      $this->context->addViolation($constraint->messageNoCommits);
    }

  }

}
