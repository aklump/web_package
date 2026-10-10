<?php

namespace AKlump\WebPackage\Tests\Unit\Helpers;

use AKlump\WebPackage\Config\Config;
use AKlump\WebPackage\Helpers\GetInfo;
use AKlump\WebPackage\Tests\TestingTraits\TempProjectTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\WebPackage\Helpers\GetInfo
 */
class GetInfoTest extends TestCase {

  use TempProjectTrait;

  protected function tearDown(): void {
    $this->removeTempProject();
  }

  public function testRelativeVersionFileIsResolvedAgainstTheRoot() {
    $root = $this->createTempProject([], [
      'info/package.yml' => "name: Lorem\ndescription: Ipsum\n",
    ]);
    $info = (new GetInfo([Config::VERSION_FILE => 'info/package.yml'], $root))();
    $this->assertSame('Lorem', $info['name']);
    $this->assertSame('Ipsum', $info['description']);
  }

  public function testAbsoluteVersionFileIgnoresTheRoot() {
    $root = $this->createTempProject([], [
      'info/package.json' => '{"name": "Lorem"}',
    ]);
    $info = (new GetInfo([Config::VERSION_FILE => $root . '/info/package.json'], '/does/not/exist'))();
    $this->assertSame(['name' => 'Lorem'], $info);
  }

  public function testMissingFileReturnsEmptyArray() {
    $root = $this->createTempProject();
    $this->assertSame([], (new GetInfo([Config::VERSION_FILE => 'nope.yml'], $root))());
  }

  public function testUnsupportedExtensionReturnsEmptyArray() {
    $root = $this->createTempProject([], ['version.txt' => '1.2.3']);
    $this->assertSame([], (new GetInfo([Config::VERSION_FILE => 'version.txt'], $root))());
  }

  public function testEmptyYamlReturnsEmptyArray() {
    $root = $this->createTempProject([], ['empty.yml' => '']);
    $this->assertSame([], (new GetInfo([Config::VERSION_FILE => 'empty.yml'], $root))());
  }

  public function testScalarYamlThrows() {
    $root = $this->createTempProject([], ['scalar.yml' => 'lorem']);
    $this->expectException(\UnexpectedValueException::class);
    (new GetInfo([Config::VERSION_FILE => 'scalar.yml'], $root))();
  }

  public function testScalarJsonThrows() {
    $root = $this->createTempProject([], ['scalar.json' => '"lorem"']);
    $this->expectException(\UnexpectedValueException::class);
    (new GetInfo([Config::VERSION_FILE => 'scalar.json'], $root))();
  }

  public function testAuthorIsDerivedFromComposerAuthors() {
    $root = $this->createTempProject([], [
      'composer.json' => json_encode([
        'authors' => [
          ['name' => 'Aaron', 'email' => 'aaron@example.com'],
          ['name' => 'Other'],
        ],
      ]),
    ]);
    $info = (new GetInfo([Config::VERSION_FILE => 'composer.json'], $root))();
    $this->assertSame('Aaron <aaron@example.com>', $info['author']);
  }

  public function testAuthorFromComposerAuthorsWithoutEmail() {
    $root = $this->createTempProject([], [
      'composer.json' => json_encode(['authors' => [['name' => 'Aaron']]]),
    ]);
    $info = (new GetInfo([Config::VERSION_FILE => 'composer.json'], $root))();
    $this->assertSame('Aaron', $info['author']);
  }

  public function testExplicitAuthorIsNotOverwritten() {
    $root = $this->createTempProject([], [
      'composer.json' => json_encode([
        'author' => 'Explicit',
        'authors' => [['name' => 'Aaron']],
      ]),
    ]);
    $info = (new GetInfo([Config::VERSION_FILE => 'composer.json'], $root))();
    $this->assertSame('Explicit', $info['author']);
  }

}
