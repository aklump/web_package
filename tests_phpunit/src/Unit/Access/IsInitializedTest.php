<?php

namespace AKlump\WebPackage\Tests\Unit\Access;

use AKlump\WebPackage\Access\IsInitialized;
use AKlump\WebPackage\Tests\TestingTraits\TempProjectTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\WebPackage\Access\IsInitialized
 */
class IsInitializedTest extends TestCase {

  use TempProjectTrait;

  protected function tearDown(): void {
    $this->removeTempProject();
  }

  public function testAccessIsTrueWhenInstallDirExists() {
    $root = $this->createTempProject(['name' => 'Lorem']);
    $this->assertTrue((new IsInitialized($root))->access());
  }

  public function testAccessIsFalseWhenInstallDirIsMissing() {
    $root = $this->createTempProject();
    $this->assertFalse((new IsInitialized($root))->access());
  }

  public function testAccessIsFalseWhenRootDoesNotExist() {
    $root = $this->createTempProject();
    $this->assertFalse((new IsInitialized($root . '/does_not_exist'))->access());
  }

  public function testAccessIgnoresTheCurrentWorkingDirectory() {
    $root = $this->createTempProject();
    $this->assertNotSame($root, getcwd());
    $this->assertFalse((new IsInitialized($root))->access());
  }

}
