<?php

namespace AKlump\WebPackage\Tests\Unit\Helpers;

use AKlump\WebPackage\Config\Config;
use AKlump\WebPackage\Config\ConfigDefaults;
use AKlump\WebPackage\Helpers\GetHookEvent;
use AKlump\WebPackage\Tests\TestingTraits\TempProjectTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\WebPackage\Helpers\GetHookEvent
 * @uses \AKlump\WebPackage\Helpers\GetInfo
 * @uses \AKlump\WebPackage\Hooks\HookEvent
 */
class GetHookEventTest extends TestCase {

  use TempProjectTrait;

  protected function tearDown(): void {
    $this->removeTempProject();
  }

  public function testEventIsPopulatedFromTheVersionFileAndTheRoot() {
    $root = $this->createTempProject([], [
      '.web_package/config.yml' => "name: Lorem\ndescription: Ipsum\nhomepage: https://example.com\nauthor: Dolor\n",
    ]);
    $config = [Config::VERSION_FILE => $root . '/.web_package/config.yml'];
    $event = (new GetHookEvent($config, $root))();
    $this->assertSame('Lorem', $event->getPackageName());
    $this->assertSame('Ipsum', $event->getDescription());
    $this->assertSame('https://example.com', $event->getHomepage());
    $this->assertSame('Dolor', $event->getAuthor());
    $this->assertSame($root, $event->getRoot());
    $this->assertSame($root . '/.web_package/config.yml', $event->getInfoFile());
  }

  public function testRelativeVersionFileBecomesAnAbsoluteInfoFile() {
    $root = $this->createTempProject([], ['info.yml' => "name: Lorem\n"]);
    $event = (new GetHookEvent([Config::VERSION_FILE => 'info.yml'], $root))();
    $this->assertSame($root . '/info.yml', $event->getInfoFile());
    $this->assertSame('Lorem', $event->getPackageName());
  }

  public function testMissingVersionFileConfigFallsBackToTheDefault() {
    $root = $this->createTempProject();
    $event = (new GetHookEvent([], $root))();
    $this->assertSame($root . '/' . ConfigDefaults::VERSION_FILE, $event->getInfoFile());
    $this->assertSame('', $event->getPackageName());
  }

}
