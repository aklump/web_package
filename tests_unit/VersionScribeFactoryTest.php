<?php

namespace AKlump\WebPackage\Tests;

use AKlump\WebPackage\Config\Config;
use AKlump\WebPackage\VersionScribeFactory;
use AKlump\WebPackage\VersionScribes\DrupalInfo;
use AKlump\WebPackage\VersionScribes\IniFile;
use AKlump\WebPackage\VersionScribes\Json;
use AKlump\WebPackage\VersionScribes\SymfonyConsoleApplication;
use AKlump\WebPackage\VersionScribes\Text;
use AKlump\WebPackage\VersionScribes\Yaml;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\WebPackage\VersionScribeFactory
 */
class VersionScribeFactoryTest extends TestCase {

  use \AKlump\WebPackage\Tests\TestingTraits\TestWithConfigTrait;

  public function dataFortestInvokeReturnsTheExpectedClassProvider() {
    $tests = [];
    $tests[] = [
      Text::class,
      '.version',
    ];
    $tests[] = [
      Text::class,
      'version.txt',
    ];
    $tests[] = [
      SymfonyConsoleApplication::class,
      'symfonyapp.php',
    ];
    $tests[] = [
      Json::class,
      'composer.json',
    ];
    $tests[] = [
      Json::class,
      'file.json',
    ];
    $tests[] = [
      IniFile::class,
      'file.ini',
    ];
    $tests[] = [
      DrupalInfo::class,
      'block.info',
    ];
    $tests[] = [
      Yaml::class,
      'file.yaml',
    ];
    $tests[] = [
      Yaml::class,
      'file.yml',
    ];

    return $tests;
  }

  /**
   * @dataProvider dataFortestInvokeReturnsTheExpectedClassProvider
   */
  public function testInvokeReturnsTheExpectedClass($expected, $subject) {
    $factory = new VersionScribeFactory($this->createLoadConfigMock([
      Config::VERSION_FILE => __DIR__ . "/files/$subject",
    ]));
    $this->assertSame($expected, get_class($factory()));
  }

  public function testNonExistentFileReturnsNull() {
    $factory = new VersionScribeFactory($this->createLoadConfigMock([
      Config::VERSION_FILE => 'foo/bar.xyz',
    ]));
    $this->assertNull($factory());
  }

}
