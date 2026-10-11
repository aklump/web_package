<?php

namespace AKlump\WebPackage\Tests\Unit\Hooks;

use AKlump\WebPackage\Hooks\HookEvent;
use AKlump\WebPackage\Hooks\HookManager;
use AKlump\WebPackage\Tests\TestingTraits\TempProjectTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @covers \AKlump\WebPackage\Hooks\HookManager
 * @uses \AKlump\WebPackage\Helpers\GetHooksDirectory
 * @uses \AKlump\WebPackage\Hooks\HookEvent
 */
class HookManagerTest extends TestCase {

  use TempProjectTrait;

  protected function tearDown(): void {
    $this->removeTempProject();
  }

  private function createManager(string $root, ?BufferedOutput $output = NULL): HookManager {
    return new HookManager($output ?? new BufferedOutput(), (new HookEvent())->setRoot($root));
  }

  public function testHooksAreFoundInTheInjectedRootAndSortedByBasename() {
    $root = $this->createTempProject([], [
      '.web_package/hooks/build/10_b.php' => '<?php',
      '.web_package/hooks/build/00_a.sh' => '',
      '.web_package/hooks/build/05_C.php' => '<?php',
      '.web_package/hooks/build/notes.txt' => '',
      '.web_package/hooks/dev/00_dev.sh' => '',
    ]);
    $found = $this->createManager($root)->findHooksByType('build');
    $this->assertSame([
      '00_a.sh',
      '05_C.php',
      '10_b.php',
    ], array_map('basename', $found));
    $this->assertSame($root . '/.web_package/hooks/build/00_a.sh', $found[0]);
  }

  public function testFilterMatchesSubstringsAndExtensions() {
    $root = $this->createTempProject([], [
      '.web_package/hooks/build/00_lorem.sh' => '',
      '.web_package/hooks/build/00_lorem.php' => '<?php',
      '.web_package/hooks/build/10_ipsum.php' => '<?php',
    ]);
    $manager = $this->createManager($root);
    $this->assertSame(['00_lorem.php', '00_lorem.sh'], array_map('basename', $manager->findHooksByType('build', 'lor')));
    $this->assertSame(['00_lorem.php', '10_ipsum.php'], array_map('basename', $manager->findHooksByType('build', '.php')));
    $this->assertSame(['10_ipsum.php'], array_map('basename', $manager->findHooksByType('build', '*ips*')));
    $this->assertSame([], $manager->findHooksByType('build', 'dolor'));
  }

  public function testHooksOfAnotherRootAreNotFound() {
    $root = $this->createTempProject([], ['.web_package/hooks/build/00_a.sh' => '']);
    $this->assertSame([], $this->createManager($root . '/elsewhere')->findHooksByType('build'));
  }

  public function testRunReportsWhenNoHookMatchesTheFilter() {
    $root = $this->createTempProject([], ['.web_package/hooks/build/00_a.sh' => '']);
    $output = new BufferedOutput();
    $this->createManager($root, $output)->run('build', 'zzz');
    $this->assertStringContainsString('There are no build-type hooks to run matching "zzz".', $output->fetch());
  }

  public function testRunReportsWhenTheHooksDirectoryIsEmpty() {
    $root = $this->createTempProject();
    $output = new BufferedOutput();
    $this->createManager($root, $output)->run('build', '');
    $this->assertStringContainsString('is empty; nothing to run.', $output->fetch());
  }

}
