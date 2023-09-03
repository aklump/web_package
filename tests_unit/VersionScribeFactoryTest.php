<?php

namespace AKlump\WebPackage\Tests;

use AKlump\WebPackage\VersionScribeFactory;
use AKlump\WebPackage\VersionScribes\DrupalInfo;
use AKlump\WebPackage\VersionScribes\IniFile;
use AKlump\WebPackage\VersionScribes\Json;
use AKlump\WebPackage\VersionScribes\SymfonyConsoleApplication;
use AKlump\WebPackage\VersionScribes\Yaml;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\WebPackage\VersionScribeFactory
 */
class VersionScribeFactoryTest extends TestCase {

  public function dataFortestInvokeReturnsTheExpectedClassProvider() {
    $tests = [];
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
    $factory = new VersionScribeFactory();
    $subject = __DIR__ . "/files/$subject";
    $this->assertSame($expected, get_class($factory($subject)));
  }

  public function testNonExistentFileThrows() {
    $factory = new VersionScribeFactory();
    $this->expectException(\InvalidArgumentException::class);
    $factory('foo/bar.xyz');
  }
}
