<?php

namespace AKlump\WebPackage\Tests\VersionScribes;

use AKlump\WebPackage\Traits\ReaderTrait;
use AKlump\WebPackage\VersionScribeInterface;
use PHPUnit\Framework\TestCase;
use z4kn4fein\SemVer\Version;

/**
 * @covers \AKlump\WebPackage\Traits\ReaderTrait
 */
class ReaderTest extends TestCase {

  public function dataForTestGetVersionReturnsExpectedProvider() {
    $tests = [];
    $tests[] = [
      '1',
      '1.0.0',
    ];
    $tests[] = [
      '3.1',
      '3.1.0',
    ];
    $tests[] = [
      'v0.0.1',
      '0.0.1',
    ];

    return $tests;
  }

  /**
   * @dataProvider dataForTestGetVersionReturnsExpectedProvider
   */
  public function testGetVersionReturnsExpected($subject, $expected) {
    $reader = new Reader();
    $result = $reader->getVersion($subject);
    $this->assertSame($expected, (string) $result);
  }

  public function testGetVersionWithBogusValueReturnsDefaultVersion() {
    $reader = new Reader();
    $result = $reader->getVersion('lorem');
    $this->assertSame(VersionScribeInterface::DEFAULT, (string) $result);
  }

  public function testGetVersionWithEmptyReturnsDefaultVersion() {
    $reader = new Reader();
    $result = $reader->getVersion('');
    $this->assertSame(VersionScribeInterface::DEFAULT, (string) $result);
  }

  public function testGetVersionReturnsExpectedClass() {
    $reader = new Reader();
    $result = $reader->getVersion('');
    $this->assertInstanceOf(Version::class, $result);
  }

}

class Reader {

  use ReaderTrait;
}
