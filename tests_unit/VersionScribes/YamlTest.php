<?php

namespace AKlump\WebPackage\Tests\VersionScribes;

use AKlump\WebPackage\Tests\Traits\WriteTestTrait;
use AKlump\WebPackage\VersionScribeInterface;
use AKlump\WebPackage\VersionScribes\Yaml;
use PHPUnit\Framework\TestCase;
use z4kn4fein\SemVer\Inc;

/**
 * @covers \AKlump\WebPackage\VersionScribes\Yaml
 */
class YamlTest extends TestCase {

  use WriteTestTrait;

  public function testWriteReplacesVersionInExistingFile() {
    $old = $this->getVersion();
    $new = $old->inc(Inc::MINOR);
    $this->unlink('yaml');
    $path = $this->getPath('yaml');
    $scribe = new Yaml($path);
    $this->assertTrue($scribe->write($old));
    $this->assertTrue($scribe->write($new));
    $this->assertSame((string) $new, (string) $scribe->read());
    $this->unlink('yaml');
  }

  public function testWriteCreatesFileAndWritesVersion() {
    $version = $this->getVersion();
    $this->unlink('yaml');
    $path = $this->getPath('yaml');
    $scribe = new Yaml($path);
    $this->assertTrue($scribe->write($version));
    $this->assertSame((string) $version, (string) $scribe->read());
    $this->unlink('yaml');
  }

  public function testYamlReturnsValueOfVersionKey() {
    $scribe = new Yaml(__DIR__ . '/../files/file.yml');
    $version = $scribe->read();
    $this->assertSame('9.0.0', (string) $version);
  }

  public function testYamlWithoutVersionReturnsDefaul() {
    $scribe = new Yaml(__DIR__ . '/../files/file2.yml');
    $version = $scribe->read();
    $this->assertSame(VersionScribeInterface::DEFAULT, (string) $version);
  }

}
