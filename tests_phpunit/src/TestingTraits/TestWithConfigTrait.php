<?php

namespace AKlump\WebPackage\Tests\TestingTraits;


use AKlump\WebPackage\Config\LoadConfig;

trait TestWithConfigTrait {

  /**
   * Get a mocked LoadConfig instance.
   *
   * @param array $config
   *   The configuration array to test with.
   */
  public function createLoadConfigMock(array $config) {
    return $this->createConfiguredMock(LoadConfig::class, [
      '__invoke' => $config,
    ]);
  }

}
