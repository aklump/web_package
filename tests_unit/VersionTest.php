<?php

namespace AKlump\WebPackage\Tests;

use AKlump\WebPackage\Model\Version;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\WebPackage\Model\Version
 */
class VersionTest extends TestCase {

  public function testVersionUpdateReturnsNewInstance() {
    $first = Version::parse('8.x-1.7.5');
    $second = $first->getNextPatchVersion();
    $this->assertNotSame($first, $second);
  }

  public function dataFortestParseProvider() {
    $tests = [];

    // INPUT, OUTPUT
    $tests[] = ['8.x-1.1', '8.x-1.1.0'];
    $tests[] = ['1.2.3', '1.2.3'];
    $tests[] = ['10.3', '10.3.0'];
    $tests[] = ['7', '7.0.0'];

    return $tests;
  }

  /**
   * @dataProvider dataFortestParseProvider
   */
  public function testParse(string $subject, string $expected) {
    $version = Version::parse($subject);
    $this->assertSame($expected, (string) $version);
  }

}
