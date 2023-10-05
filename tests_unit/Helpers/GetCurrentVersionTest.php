<?php

namespace AKlump\WebPackage\Tests\Helpers;

use AKlump\WebPackage\Config\Config;
use AKlump\WebPackage\Config\ConfigDefaults;
use AKlump\WebPackage\Helpers\GetCurrentVersion;
use AKlump\WebPackage\VersionScribeInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\WebPackage\Helpers\GetCurrentVersion
 */
class GetCurrentVersionTest extends TestCase {

  public function testScribeReadVersionIsReturnedWhenExpected() {
    $config = [Config::INITIAL_VERSION => '2.1.9'];
    $scribe = $this->createConfiguredMock(VersionScribeInterface::class, [
      'read' => '5.0',
    ]);
    $this->assertSame('5.0', (new GetCurrentVersion($config, $scribe))());
  }

  public function testInitialVersionIsReturnedWhenExpected() {
    $config = [Config::INITIAL_VERSION => '2.1.9'];
    $scribe = $this->createConfiguredMock(VersionScribeInterface::class, [
      'read' => '',
    ]);
    $this->assertSame('2.1.9', (new GetCurrentVersion($config, $scribe))());
  }

  public function testConfigDefaultsIsReturnedWhenExpected() {
    $config = [];
    $scribe = $this->createConfiguredMock(VersionScribeInterface::class, [
      'read' => '',
    ]);
    $this->assertSame(ConfigDefaults::INITIAL_VERSION, (new GetCurrentVersion($config, $scribe))());
  }

}
