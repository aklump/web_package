<?php

namespace AKlump\WebPackage\Helpers;

use AKlump\WebPackage\Model\Context;
use AKlump\WebPackage\Traits\HasContextTrait;

class GetPreviousVersion {

  const STASH_KEY = 'version';

  use HasContextTrait;

  public function __construct(Context $context) {
    $this->setContext($context);
  }

  public function __invoke(): ?string {
    return (new Stash($this->getContext()))->read(self::STASH_KEY) ?? NULL;
  }

}
