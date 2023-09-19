<?php

namespace AKlump\WebPackage\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

class VersionDegree extends Constraint {

  public $message = 'The version degree must be one "{{ degrees }}".';

  public $degrees = [];

  /**
   * @param array $degrees
   * @param array|NULL $groups
   * @param $payload
   *
   * @see \AKlump\WebPackage\Helpers\VersionDegree::MAJOR
   * @see \AKlump\WebPackage\Helpers\VersionDegree::MINOR
   * @see \AKlump\WebPackage\Helpers\VersionDegree::PATCH
   */
  public function __construct(array $degrees, array $groups = NULL, $payload = NULL) {
    $this->degrees = $degrees;
    parent::__construct([], $groups, $payload);
  }

}
