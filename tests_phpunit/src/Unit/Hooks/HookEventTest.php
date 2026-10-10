<?php

namespace AKlump\WebPackage\Tests\Unit\Hooks;

use AKlump\WebPackage\Hooks\HookEvent;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\WebPackage\Hooks\HookEvent
 * @uses \AKlump\WebPackage\Helpers\GetWebPackageRoot
 */
class HookEventTest extends TestCase {

  public function testRootAndInfoFileAreEmptyUntilSet() {
    $event = new HookEvent();
    $this->assertSame('', $event->getRoot());
    $this->assertSame('', $event->getInfoFile());
  }

  public function testRootAndInfoFileReturnWhatWasSet() {
    $event = new HookEvent();
    $this->assertSame($event, $event->setRoot('/path/to/project'));
    $this->assertSame($event, $event->setInfoFile('/path/to/project/info.yml'));
    $this->assertSame('/path/to/project', $event->getRoot());
    $this->assertSame('/path/to/project/info.yml', $event->getInfoFile());
  }

  public function testEachEventHasItsOwnRoot() {
    $a = (new HookEvent())->setRoot('/a');
    $b = (new HookEvent())->setRoot('/b');
    $this->assertSame('/a', $a->getRoot());
    $this->assertSame('/b', $b->getRoot());
  }

  public function testWebPackageRootIsTheApplicationDirectory() {
    $root = (new HookEvent())->getWebPackageRoot();
    $this->assertFileExists($root . '/includes/hook_runner.sh');
  }

  public function testScalarGettersAndSetters() {
    $event = new HookEvent();
    $this->assertSame('', $event->getVersion());
    $this->assertSame('', $event->getPreviousVersion());
    $this->assertSame('', $event->getPackageName());
    $this->assertSame('', $event->getDescription());
    $this->assertSame('', $event->getHomepage());
    $this->assertSame('', $event->getAuthor());
    $this->assertSame('', $event->getHook());

    $event->setVersion('1.2.3+4')
      ->setPreviousVersion('1.2.2')
      ->setPackageName('Lorem')
      ->setDescription('Ipsum')
      ->setHomepage('https://example.com')
      ->setAuthor('Dolor');
    $this->assertSame('/path/to/hook.sh', $event->setHook('/path/to/hook.sh'));

    $this->assertSame('1.2.3+4', $event->getVersion());
    $this->assertSame('1.2.2', $event->getPreviousVersion());
    $this->assertSame('Lorem', $event->getPackageName());
    $this->assertSame('Ipsum', $event->getDescription());
    $this->assertSame('https://example.com', $event->getHomepage());
    $this->assertSame('Dolor', $event->getAuthor());
    $this->assertSame('/path/to/hook.sh', $event->getHook());
  }

  public function testGetDateTimeReturnsANonEmptyString() {
    $this->assertNotSame('', (new HookEvent())->getDateTime());
  }

}
