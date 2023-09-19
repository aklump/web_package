<?php

namespace AKlump\WebPackage\Traits;


use AKlump\WebPackage\Model\Context;

trait HasContextTrait {

  private $context;

  public function getContext(): Context {
    return $this->context;
  }

  public function setContext(Context $context) {
    $this->context = $context;
  }

}
