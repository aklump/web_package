<?php

namespace AKlump\WebPackage\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

class VersionDegree extends Constraint {

  public $message = 'The version degree must be one "{{ degrees }}".';

  public $degrees = [];

  public function __construct(array $degrees, array $groups = NULL, $payload = NULL) {
    $this->degrees = $degrees;
    parent::__construct([], $groups, $payload);
  }

}
