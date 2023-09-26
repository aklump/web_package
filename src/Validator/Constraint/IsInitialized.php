<?php

namespace AKlump\WebPackage\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

class IsInitialized extends Constraint {

  public $message = 'Your project is not initialized; (bump init) and try again.';

}
