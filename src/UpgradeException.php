<?php

namespace AKlump\WebPackage;

/**
 * Throw this when you want the user to run the upgrade command.
 *
 * When you suspect/detect that upgrade of configuration or other aspects are
 * needed, such as missing config keys.
 */
class UpgradeException extends \RuntimeException {

  public function __construct() {
    parent::__construct(sprintf('Something went wrong, try "bump upgrade" for more info.'));
  }
}
