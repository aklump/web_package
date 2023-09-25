<?php

namespace AKlump\WebPackage\Tests\Helpers;

use AKlump\WebPackage\Config\Config;
use AKlump\WebPackage\Helpers\ShouldCreateTag;
use AKlump\WebPackage\Helpers\VersionDegree;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\WebPackage\Helpers\ShouldCreateTag
 */
class ShouldCreateTagTest extends TestCase {

  public function dataFortestInvokeProvider() {
    $tests = [];
    $tests[] = [
      TRUE,
      TRUE,
      '1.1.1',
      '1.1.1',
    ];
    $tests[] = [
      TRUE,
      'yes',
      '1.1.1',
      '1.1.1',
    ];
    $tests[] = [
      FALSE,
      FALSE,
      '1.1.1',
      '1.1.1',
    ];
    $tests[] = [
      FALSE,
      'no',
      '1.1.1',
      '1.1.1',
    ];
    $tests[] = [
      TRUE,
      VersionDegree::PATCH,
      '1.1.1',
      '1.1.2',
    ];
    $tests[] = [
      TRUE,
      VersionDegree::PATCH,
      '1.1.1',
      '1.2.0',
    ];
    $tests[] = [
      TRUE,
      VersionDegree::PATCH,
      '1.1.1',
      '2.0.0',
    ];
    $tests[] = [
      FALSE,
      VersionDegree::MINOR,
      '1.1.1',
      '1.1.2',
    ];
    $tests[] = [
      TRUE,
      VersionDegree::MINOR,
      '1.1.1',
      '1.2.0',
    ];
    $tests[] = [
      TRUE,
      VersionDegree::MINOR,
      '1.1.1',
      '2.0.0',
    ];
    $tests[] = [
      FALSE,
      VersionDegree::MAJOR,
      '1.1.1',
      '1.1.2',
    ];
    $tests[] = [
      FALSE,
      VersionDegree::MAJOR,
      '1.1.1',
      '1.2.0',
    ];
    $tests[] = [
      TRUE,
      VersionDegree::MAJOR,
      '1.1.1',
      '2.0.0',
    ];

    return $tests;
  }

  /**
   * @dataProvider dataFortestInvokeProvider
   */
  public function testInvoke($expected, $config_value, $old_version, $new_version) {
    $config = [Config::CREATE_TAGS => $config_value];
    $this->assertSame($expected, (new ShouldCreateTag($config))($old_version, $new_version));
  }

}
