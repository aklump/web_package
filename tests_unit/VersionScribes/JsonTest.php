<?php

namespace AKlump\WebPackage\Tests\VersionScribes;

use AKlump\WebPackage\Tests\WriteTestTrait;
use AKlump\WebPackage\VersionScribeInterface;
use AKlump\WebPackage\VersionScribes\Json;
use PHPUnit\Framework\TestCase;
use z4kn4fein\SemVer\Inc;
use z4kn4fein\SemVer\Version;

/**
 * @covers \AKlump\WebPackage\VersionScribes\Json
 */
class JsonTest extends TestCase {

  use WriteTestTrait;

  public function testWriteReplacesVersionInExistingFile() {
    $path = $this->getPath('json');
    copy(__DIR__ . '/../files/file.json', $path);
    $expected_size = $this->filesize($path);
    $scribe = new Json($path);
    $old = Version::parse($scribe->read(), FALSE);
    $new = $old->inc(Inc::PATCH);
    $this->assertTrue($scribe->write($new));
    $this->assertTrue($new->isEqual(Version::parse($scribe->read(), FALSE)));
    $this->assertSame($expected_size, $this->filesize($path));
    $this->unlink('json');
  }

  public function testWriteCreatesFileAndWritesVersion() {
    $version = $this->getVersion();
    $this->unlink('json');
    $path = $this->getPath('json');
    $scribe = new Json($path);
    $this->assertTrue($scribe->write($version));
    $this->assertSame((string) $version, $scribe->read());
    $this->unlink('json');
  }

  public function testJsonReturnsValueOfVersionKey() {
    $scribe = new Json(__DIR__ . '/../files/composer.json');
    $version = $scribe->read();
    $this->assertSame('1.2.3', $version);
  }

  public function testJsonWithoutVersionReturnsEmptyString() {
    $scribe = new Json(__DIR__ . '/../files/composer2.json');
    $version = $scribe->read();
    $this->assertSame('', $version);
  }

}
