<?php

namespace AKlump\WebPackage\Tests\Unit\VersionScribes;

use AKlump\WebPackage\Tests\TestingTraits\WriteTestTrait;
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

  public function testReadWriteBuildMetadata() {
    $path = $this->getPath('info');
    copy(__DIR__ . '/../files/build.info', $path);
    $scribe = new DrupalInfo($path);
    $this->assertSame('1.2.3+4', $scribe->read());
    $this->assertTrue($scribe->write('1.2.4+5'));
    $this->assertSame('1.2.4+5', $scribe->read());
    $this->assertStringNotContainsString('+4', file_get_contents($path));
    $this->unlink('info');
  }

}
