<?php

namespace AKlump\WebPackage\Validator\Constraint;

use AKlump\WebPackage\Helpers\GetRootPath;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class IsInitializedValidator extends ConstraintValidator {


  /**
   * @inheritDoc
   */
  public function validate($value, Constraint $constraint) {
    if (!$constraint instanceof IsInitialized) {
      throw new UnexpectedValueException($constraint, IsInitialized::class);
    }
    if (getenv('WEB_PACKAGE_ENV') === 'testing') {
      return;
    }
    $root_path = (new GetRootPath())($value);
    if (!file_exists($root_path . '/.web_package')) {
      $this->context->addViolation($constraint->message);
    }
  }
}
