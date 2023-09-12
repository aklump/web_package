<?php

namespace AKlump\WebPackage\Validator\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class VersionDegreeValidator extends ConstraintValidator {

  use \AKlump\WebPackage\Traits\ImplodeTrait;

  /**
   * @inheritDoc
   */
  public function validate($value, Constraint $constraint) {
    if (!$constraint instanceof VersionDegree) {
      throw new UnexpectedTypeException($constraint, VersionDegree::class);
    }
    if (NULL === $value || '' === $value) {
      return;
    }
    if (!in_array($value, $constraint->degrees)) {
      $this->context
        ->buildViolation($constraint->message)
        ->setParameters(['{{ degrees }}' => $this->or($constraint->degrees)])
        ->addViolation();
    }
  }

}
