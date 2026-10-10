<?php

namespace AKlump\WebPackage\Tests\Unit\Command;

use AKlump\WebPackage\Command\BuildCommand;
use AKlump\WebPackage\Command\DevCommand;
use AKlump\WebPackage\Command\PublishCommand;
use AKlump\WebPackage\Command\UnBuildCommand;
use AKlump\WebPackage\Config\Config;
use AKlump\WebPackage\Git\GitProxy;
use AKlump\WebPackage\Model\Context;
use AKlump\WebPackage\Tests\TestingTraits\TempProjectTrait;
use AKlump\WebPackage\Tests\TestingTraits\TestWithConfigTrait;
use AKlump\WebPackage\VersionScribeFactory;
use League\Container\Container;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers \AKlump\WebPackage\Command\BaseHooksCommand
 * @uses \AKlump\WebPackage\Helpers\GetBuildNumber
 * @uses \AKlump\WebPackage\Helpers\GetCurrentVersion
 * @uses \AKlump\WebPackage\Helpers\GetHookEvent
 * @uses \AKlump\WebPackage\Helpers\GetHooksDirectory
 * @uses \AKlump\WebPackage\Helpers\GetInfo
 * @uses \AKlump\WebPackage\Helpers\GetWebPackageRoot
 * @uses \AKlump\WebPackage\Helpers\StampVersion
 * @uses \AKlump\WebPackage\Hooks\HookEvent
 * @uses \AKlump\WebPackage\Hooks\HookManager
 * @uses \AKlump\WebPackage\Model\Version
 * @uses \AKlump\WebPackage\Traits\HasConfigTrait
 * @uses \AKlump\WebPackage\Traits\WriterTrait
 * @uses \AKlump\WebPackage\VersionScribeFactory
 * @uses \AKlump\WebPackage\VersionScribes\MissingFileScribe
 * @uses \AKlump\WebPackage\VersionScribes\Yaml
 */
class BuildCommandTest extends TestCase {

  use TempProjectTrait;
  use TestWithConfigTrait;

  const HOOK = <<<'SH'
printf '%s' "$VERSION" > "$ROOT/hook_version.txt"
printf '%s' "$2" > "$ROOT/hook_arg.txt"
SH;

  protected function tearDown(): void {
    $this->removeTempProject();
  }

  /**
   * @param string $root
   * @param array $config
   * @param \AKlump\WebPackage\Git\GitProxy $git
   *
   * @return \League\Container\Container
   *   A container with the services the hook commands ask for.  The version
   *   scribe is the real one, reading and writing the temp project's file.
   */
  private function createContainer(string $root, array $config, GitProxy $git): Container {
    $config += [
      Config::VERSION_FILE => $root . '/.web_package/config.yml',
      'git_root' => '.',
    ];
    $loader = $this->createLoadConfigMock($config);
    $context = $this->createConfiguredMock(Context::class, ['getRootPath' => $root]);
    $factory = new VersionScribeFactory($loader);

    // The container invokes a callable service when resolving it, so closures
    // are used to hand back the invokable objects themselves.
    $container = new Container();
    $container->add('config.loader', function () use ($loader) {
      return $loader;
    });
    $container->add('context', $context);
    $container->add('git', $git);
    $container->add('scribe.factory', function () use ($factory) {
      return $factory;
    });

    return $container;
  }

  private function createProject(): string {
    return $this->createTempProject([], [
      '.web_package/config.yml' => "version: \"3.3.3\"\n",
      '.web_package/hooks/build/00_record.sh' => self::HOOK,
      '.web_package/hooks/dev/00_record.sh' => self::HOOK,
      '.web_package/hooks/publish/00_record.sh' => self::HOOK,
      '.web_package/hooks/unbuild/00_record.sh' => self::HOOK,
    ]);
  }

  /**
   * @return \AKlump\WebPackage\Git\GitProxy
   *   Git reports one release tag, 3.3.3, and 17 commits since.
   */
  private function createGit(): GitProxy {
    $git = $this->createMock(GitProxy::class);
    $git->method('getTagsByCreationDate')->willReturn(['3.3.3']);
    $git->method('getFilesInCommit')->willReturn(['src/a.php']);
    $git->method('countCommits')->willReturn(17);

    return $git;
  }

  public function testBuildStampsTheVersionFileAndHooksSeeTheStamp() {
    $root = $this->createProject();
    $git = $this->createGit();
    $git->expects($this->once())->method('countCommits')->with('3.3.3..HEAD');
    $command = new BuildCommand($this->createContainer($root, [Config::BUILD_NUMBER => TRUE], $git));

    $tester = new CommandTester($command);
    $status = $tester->execute([]);

    $this->assertSame(Command::SUCCESS, $status);
    $this->assertStringContainsString('Build number: 3.3.3+17', $tester->getDisplay());
    $this->assertStringContainsString('3.3.3+17', file_get_contents($root . '/.web_package/config.yml'));
    $this->assertSame('3.3.3+17', file_get_contents($root . '/hook_version.txt'));
    $this->assertSame('3.3.3+17', file_get_contents($root . '/hook_arg.txt'));
  }

  public function testBuildingTwiceGivesTheSameBuildNumber() {
    $root = $this->createProject();
    $container = $this->createContainer($root, [Config::BUILD_NUMBER => TRUE], $this->createGit());

    (new CommandTester(new BuildCommand($container)))->execute([]);
    (new CommandTester(new BuildCommand($container)))->execute([]);

    $this->assertSame("version: \"3.3.3+17\"\n", file_get_contents($root . '/.web_package/config.yml'));
    $this->assertSame('3.3.3+17', file_get_contents($root . '/hook_version.txt'));
  }

  /**
   * @dataProvider dataFortestNothingIsStampedProvider
   */
  public function testNothingIsStamped(string $command_class, array $config) {
    $root = $this->createProject();
    $git = $this->createMock(GitProxy::class);
    $git->expects($this->never())->method('getTagsByCreationDate');
    $git->expects($this->never())->method('countCommits');
    $command = new $command_class($this->createContainer($root, $config, $git));

    $tester = new CommandTester($command);
    $this->assertSame(Command::SUCCESS, $tester->execute([]));

    $this->assertStringNotContainsString('Build number', $tester->getDisplay());
    $this->assertSame("version: \"3.3.3\"\n", file_get_contents($root . '/.web_package/config.yml'));
    $this->assertSame('3.3.3', file_get_contents($root . '/hook_version.txt'));
  }

  public function dataFortestNothingIsStampedProvider() {
    $tests = [];
    $tests['build, key is absent'] = [BuildCommand::class, []];
    $tests['build, key is off'] = [BuildCommand::class, [Config::BUILD_NUMBER => FALSE]];
    $tests['dev, key is on'] = [DevCommand::class, [Config::BUILD_NUMBER => TRUE]];
    $tests['publish, key is on'] = [PublishCommand::class, [Config::BUILD_NUMBER => TRUE]];
    $tests['unbuild, key is on'] = [UnBuildCommand::class, [Config::BUILD_NUMBER => TRUE]];

    return $tests;
  }

  public function testBuildFailsWhenTheBuildNumberCannotBeWritten() {
    $root = $this->createTempProject([], ['.web_package/hooks/build/00_record.sh' => self::HOOK]);
    $command = new BuildCommand($this->createContainer($root, [
      Config::BUILD_NUMBER => TRUE,
      Config::VERSION_FILE => $root . '/.web_package/missing.yml',
    ], $this->createGit()));

    $this->expectException(\RuntimeException::class);
    (new CommandTester($command))->execute([]);
  }

}
