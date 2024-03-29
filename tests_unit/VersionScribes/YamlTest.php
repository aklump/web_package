<?php

namespace AKlump\WebPackage\Tests\VersionScribes;

use AKlump\WebPackage\Tests\WriteTestTrait;
use AKlump\WebPackage\VersionScribeInterface;
use AKlump\WebPackage\VersionScribes\Yaml;
use PHPUnit\Framework\TestCase;
use z4kn4fein\SemVer\Inc;
use z4kn4fein\SemVer\Version;

/**
 * @covers \AKlump\WebPackage\VersionScribes\Yaml
 */
class YamlTest extends TestCase {

  use WriteTestTrait;

  public function testWriteAddsVersionInExistingFileWithoutVersion() {
    $path = $this->getPath('yml');
    file_put_contents($path, \Symfony\Component\Yaml\Yaml::dump(['name' => 'lorem']));
    $scribe = new Yaml($path);
    $this->assertTrue($scribe->write('0.2.19'));
    $this->assertSame('0.2.19', $scribe->read());
    $this->unlink('yml');
  }

  public function testWriteReplacesVersionInExistingFile() {
    $path = $this->getPath('yml');
    copy(__DIR__ . '/../files/file.yml', $path);
    $expected_size = $this->filesize($path);
    $scribe = new Yaml($path);
    $old = Version::parse($scribe->read(), FALSE);
    $new = $old->inc(Inc::MAJOR);
    $this->assertTrue($scribe->write($new));
    $this->assertTrue($new->isEqual(Version::parse($scribe->read(), FALSE)));
    $this->assertSame($expected_size, $this->filesize($path));
    $this->unlink('yml');
  }

  public function testWriteCreatesFileAndWritesVersion() {
    $version = $this->getVersion();
    $this->unlink('yaml');
    $path = $this->getPath('yaml');
    $scribe = new Yaml($path);
    $this->assertTrue($scribe->write($version));
    $this->assertSame((string) $version, $scribe->read());
    $this->unlink('yaml');
  }

  public function testYamlReturnsValueOfVersionKey() {
    $scribe = new Yaml(__DIR__ . '/../files/file.yml');
    $version = $scribe->read();
    $this->assertSame('8', $version);
  }

  public function testYamlWithoutVersionReturnsEmptyString() {
    $scribe = new Yaml(__DIR__ . '/../files/file2.yml');
    $version = $scribe->read();
    $this->assertSame('', $version);
  }

}
