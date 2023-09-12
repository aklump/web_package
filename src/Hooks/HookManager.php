<?php

namespace AKlump\WebPackage\Hooks;

class HookManager {

  /**
   * @param array $context
   * @param string $filter
   *
   * @return mixed
   * @throws \AKlump\WebPackage\HookException
   */
  public function run(array $context, string $filter = '') {
    throw new \AKlump\WebPackage\HookException('@todo');
  }

}
