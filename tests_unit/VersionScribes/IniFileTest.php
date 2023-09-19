<?php

namespace AKlump\WebPackage\Tests\VersionScribes;

use AKlump\WebPackage\Tests\WriteTestTrait;
use AKlump\WebPackage\VersionScribeInterface;
use AKlump\WebPackage\VersionScribes\IniFile;
use PHPUnit\Framework\TestCase;
use z4kn4fein\SemVer\Inc;
use z4kn4fein\SemVer\Version;

/**
 * @covers \AKlump\WebPackage\VersionScribes\IniFile
 */
class IniFileTest extends TestCase {

  use WriteTestTrait;

  public function testWriteReplacesVersionInExistingFile() {
    $path = $this->getPath('ini');
    copy(__DIR__ . '/../files/file.ini', $path);
    $expected_size = $this->filesize($path);
    $scribe = new IniFile($path);
    $old = Version::parse($scribe->read(), FALSE);
    $new = $old->inc(Inc::PATCH);
    $this->assertTrue($scribe->write($new));
    $this->assertTrue($new->isEqual(Version::parse($scribe->read(), FALSE)));
    $this->assertSame($expected_size, $this->filesize($path));
    $this->unlink('ini');
  }

  public function testWriteCreatesFileAndWritesVersion() {
    $version = $this->getVersion();
    $this->unlink('ini');
    $path = $this->getPath('ini');
    $scribe = new IniFile($path);
    $this->assertTrue($scribe->write($version));
    $this->assertSame((string) $version, $scribe->read());
    $this->unlink('ini');
  }

  public function testIniFileReturnsValueOfVersionKey() {
    $scribe = new IniFile(__DIR__ . '/../files/file.ini');
    $version = $scribe->read();
    $this->assertSame('2.3.4', $version);
  }

  public function testIniFileWithoutVersionReturnsDefaul() {
    $scribe = new IniFile(__DIR__ . '/../files/file2.ini');
    $version = $scribe->read();
    $this->assertNull($version);
  }

}
