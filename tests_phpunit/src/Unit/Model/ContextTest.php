<?php

namespace AKlump\WebPackage\Tests\Unit\Model;

use AKlump\WebPackage\Config\Config;
use AKlump\WebPackage\Config\LoadConfig;
use AKlump\WebPackage\Model\Context;
use AKlump\WebPackage\Model\GitFlow;
use AKlump\WebPackage\Tests\TestingTraits\TempProjectTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\WebPackage\Model\Context
 * @uses \AKlump\WebPackage\Helpers\GetBranchType
 * @uses \AKlump\WebPackage\Helpers\GetServerHome
 * @uses \AKlump\WebPackage\Helpers\GetCurrentBranch
 */
class ContextTest extends TestCase {

  use TempProjectTrait;

  protected function tearDown(): void {
    $this->removeTempProject();
  }

  public function testGetRootPathReturnsTheInjectedPath() {
    $context = new Context($this->createMock(LoadConfig::class), '/path/to/project');
    $this->assertSame('/path/to/project', $context->getRootPath());
  }

  public function testGetConfigLoadsOnceAndPassesNoTemplate() {
    $received = [];
    $loader = $this->createMock(LoadConfig::class);
    $loader->expects($this->once())
      ->method('__invoke')
      ->willReturnCallback(function () use (&$received) {
        $received[] = func_get_args();

        return [Config::MAIN_BRANCH => 'main'];
      });
    $context = new Context($loader, '/path/to/project');
    $this->assertSame([Config::MAIN_BRANCH => 'main'], $context->getConfig());
    $this->assertSame([Config::MAIN_BRANCH => 'main'], $context->getConfig());
    $this->assertCount(1, $received);
    $this->assertNull($received[0][0] ?? NULL, 'The root path must not be passed as the template.');
  }

  public function testGetBranchTypeUsesTheLoadedConfig() {
    $loader = $this->createConfiguredMock(LoadConfig::class, [
      '__invoke' => [
        Config::MAIN_BRANCH => 'trunk',
        Config::DEVELOP_BRANCH => 'dev',
      ],
    ]);
    $context = new Context($loader, '/path/to/project');
    $this->assertSame(GitFlow::MASTER, $context->getBranchType('trunk'));
    $this->assertSame(GitFlow::DEVELOP, $context->getBranchType('dev'));
    $this->assertSame(GitFlow::RELEASE, $context->getBranchType('release-1.0.0'));
    $this->assertSame(GitFlow::FEATURE, $context->getBranchType('lorem'));
  }

  public function testGetBranchTypeIsEmptyForEmptyBranchName() {
    $context = new Context($this->createMock(LoadConfig::class), '/path/to/project');
    $this->assertSame('', $context->getBranchType(''));
  }

  public function testGetServerHomeReturnsHome() {
    $this->createTempProject();
    $context = new Context($this->createMock(LoadConfig::class), '/path/to/project');
    $this->assertSame($this->getTempProjectHome(), $context->getServerHome());
  }

  public function testGetCurrentBranchReturnsAString() {
    $context = new Context($this->createMock(LoadConfig::class), '/path/to/project');
    $this->assertIsString($context->getCurrentBranch());
  }

}
