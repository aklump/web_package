<?php

namespace AKlump\WebPackage\Tests\VersionScribes;

use AKlump\WebPackage\Tests\WriteTestTrait;
use AKlump\WebPackage\VersionScribes\DrupalInfo;
use PHPUnit\Framework\TestCase;
use z4kn4fein\SemVer\Inc;
use z4kn4fein\SemVer\Version;

/**
 * @covers \AKlump\WebPackage\VersionScribes\DrupalInfo
 */
class DrupalInfoTest extends TestCase {

  use WriteTestTrait;

  public function testWriteReplacesVersionInExistingFile() {
    $path = $this->getPath('info');
    copy(__DIR__ . '/../files/block.info', $path);
    $expected_size = $this->filesize($path);
    $scribe = new DrupalInfo($path);
    $old = Version::parse($scribe->read(), FALSE);
    $new = $old->inc(Inc::MINOR);
    $this->assertTrue($scribe->write($new));
    $this->assertTrue($new->isEqual(Version::parse($scribe->read(), FALSE)));
    $this->assertSame($expected_size, $this->filesize($path));
    $this->unlink('info');
  }

  public function testWriteCreatesFileAndWritesVersion() {
    $version = $this->getVersion();
    $this->unlink('info');
    $path = $this->getPath('info');
    $scribe = new DrupalInfo($path);
    $this->assertTrue($scribe->write($version));
    $this->assertSame((string) $version, $scribe->read());
    $this->unlink('info');
  }

  public function testDrupalInfoReturnsValueOfVersionKey() {
    $scribe = new DrupalInfo(__DIR__ . '/../files/block.info');
    $version = $scribe->read();
    $this->assertSame('7.98', $version);
  }

}
