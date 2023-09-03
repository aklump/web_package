<?php

namespace AKlump\WebPackage\Tests\VersionScribes;

use AKlump\WebPackage\Tests\Traits\WriteTestTrait;
use AKlump\WebPackage\VersionScribes\DrupalInfo;
use PHPUnit\Framework\TestCase;
use z4kn4fein\SemVer\Inc;

/**
 * @covers \AKlump\WebPackage\VersionScribes\DrupalInfo
 */
class DrupalInfoTest extends TestCase {

  use WriteTestTrait;

  public function testWriteReplacesVersionInExistingFile() {
    $old = $this->getVersion();
    $new = $old->inc(Inc::MINOR);
    $this->unlink('info');
    $path = $this->getPath('info');
    $scribe = new DrupalInfo($path);
    $this->assertTrue($scribe->write($old));
    $this->assertTrue($scribe->write($new));
    $this->assertSame((string) $new, (string) $scribe->read());
    $this->unlink('info');
  }

  public function testWriteCreatesFileAndWritesVersion() {
    $version = $this->getVersion();
    $this->unlink('info');
    $path = $this->getPath('info');
    $scribe = new DrupalInfo($path);
    $this->assertTrue($scribe->write($version));
    $this->assertSame((string) $version, (string) $scribe->read());
    $this->unlink('info');
  }

  public function testDrupalInfoReturnsValueOfVersionKey() {
    $scribe = new DrupalInfo(__DIR__ . '/../files/block.info');
    $version = $scribe->read();
    $this->assertSame('7.98.0', (string) $version);
  }

}
