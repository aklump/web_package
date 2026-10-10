<?php

namespace AKlump\WebPackage\Tests\Unit\Command;

use AKlump\WebPackage\Command\DoneCommand;
use AKlump\WebPackage\Config\Config;
use AKlump\WebPackage\Git\GitProxy;
use AKlump\WebPackage\Helpers\VersionDegree;
use AKlump\WebPackage\Model\Context;
use AKlump\WebPackage\Model\GitFlow;
use AKlump\WebPackage\Tests\TestingTraits\TestWithConfigTrait;
use AKlump\WebPackage\Tests\TestingTraits\WriteTestTrait;
use AKlump\WebPackage\VersionScribeInterface;
use AKlump\WebPackage\VersionScribeFactory;
use League\Container\Container;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers \AKlump\WebPackage\Command\DoneCommand
 * @uses \AKlump\WebPackage\Helpers\GetCurrentVersion
 * @uses \AKlump\WebPackage\Helpers\GetPreviousVersion
 * @uses \AKlump\WebPackage\Helpers\GetRootPath
 * @uses \AKlump\WebPackage\Helpers\GetServerHome
 * @uses \AKlump\WebPackage\Helpers\GetVersionChangeDegree
 * @uses \AKlump\WebPackage\Helpers\ShouldCreateTag
 * @uses \AKlump\WebPackage\Helpers\Stash
 * @uses \AKlump\WebPackage\Model\GitFlow
 * @uses \AKlump\WebPackage\Model\Version
 * @uses \AKlump\WebPackage\Validator\Constraint\GitBranch
 * @uses \AKlump\WebPackage\Validator\Constraint\GitBranchValidator
 * @uses \AKlump\WebPackage\Validator\Constraint\IsInitializedValidator
 */
class DoneCommandTest extends TestCase {

  use TestWithConfigTrait;
  use WriteTestTrait;

  public function dataFortestDoneCommandProvider() {
    $tests = [];

    //
    // Begin main only, patch change, should tag, no pushing.
    //
    $config = [
      Config::MAIN_BRANCH => 'main',
      Config::PUSH_TAGS => FALSE,
      Config::PUSH_MASTER => FALSE,
    ];
    $runtime = [
      'master_version' => '0.1.5',
      'new_version' => '0.1.6',
      'current_branch' => 'release-0.1.6',
    ];
    $git = $this->createMock(GitProxy::class);
    $git->expects($this->once())->method('tag')->with($runtime['new_version']);
    $git->expects($this->once())
      ->method('mergeBranch')
      ->with($config[Config::MAIN_BRANCH], $runtime['current_branch']);
    $git->expects($this->never())->method('push');
    $git->expects($this->once())
      ->method('deleteBranch')
      ->with($runtime['current_branch']);
    $tests[] = [$config, $runtime, $git];


    //
    // Begin main only, patch change, should not tag, no pushing.
    //
    $config = [
      Config::MAIN_BRANCH => 'main',
      Config::CREATE_TAGS => VersionDegree::MINOR,
      Config::PUSH_TAGS => FALSE,
      Config::PUSH_MASTER => FALSE,
    ];
    $runtime = [
      'master_version' => '0.1.5',
      'new_version' => '0.1.6',
      'current_branch' => 'release-0.1.6',
    ];
    $git = $this->createMock(GitProxy::class);
    $git->expects($this->never())->method('tag');
    $git->expects($this->once())
      ->method('mergeBranch')
      ->with($config[Config::MAIN_BRANCH], $runtime['current_branch']);
    $git->expects($this->never())->method('push');
    $git->expects($this->once())
      ->method('deleteBranch')
      ->with($runtime['current_branch']);
    $tests[] = [$config, $runtime, $git];

    //
    // Begin main only, minor change, should tag, no pushing.
    //
    $config = [
      Config::MAIN_BRANCH => 'main',
      Config::CREATE_TAGS => VersionDegree::MINOR,
      Config::PUSH_TAGS => FALSE,
      Config::PUSH_MASTER => FALSE,
    ];
    $runtime = [
      'master_version' => '0.1.5',
      'new_version' => '0.2.0',
      'current_branch' => 'release-0.2.0',
    ];
    $git = $this->createMock(GitProxy::class);
    $git->expects($this->once())->method('tag')->with($runtime['new_version']);
    $git->expects($this->once())
      ->method('mergeBranch')
      ->with($config[Config::MAIN_BRANCH], $runtime['current_branch']);
    $git->expects($this->never())->method('push');
    $git->expects($this->once())
      ->method('deleteBranch')
      ->with($runtime['current_branch']);
    $tests[] = [$config, $runtime, $git];

    //
    // Begin main only, major change, should tag, no pushing.
    //
    $config = [
      Config::MAIN_BRANCH => 'main',
      Config::CREATE_TAGS => VersionDegree::MAJOR,
      Config::PUSH_TAGS => FALSE,
      Config::PUSH_MASTER => FALSE,
    ];
    $runtime = [
      'master_version' => '0.1.5',
      'new_version' => '1.0.0',
      'current_branch' => 'release-1.0.0',
    ];
    $git = $this->createMock(GitProxy::class);
    $git->expects($this->once())->method('tag')->with($runtime['new_version']);
    $git->expects($this->once())
      ->method('mergeBranch')
      ->with($config[Config::MAIN_BRANCH], $runtime['current_branch']);
    $git->expects($this->never())->method('push');
    $git->expects($this->once())
      ->method('deleteBranch')
      ->with($runtime['current_branch']);
    $tests[] = [$config, $runtime, $git];

    //
    // Begin main only, minor change, should not tag, no pushing.
    //
    $config = [
      Config::MAIN_BRANCH => 'main',
      Config::CREATE_TAGS => VersionDegree::MAJOR,
      Config::PUSH_TAGS => FALSE,
      Config::PUSH_MASTER => FALSE,
    ];
    $runtime = [
      'master_version' => '0.1.5',
      'new_version' => '0.2.0',
      'current_branch' => 'release-0.2.0',
    ];
    $git = $this->createMock(GitProxy::class);
    $git->expects($this->never())->method('tag');
    $git->expects($this->once())
      ->method('mergeBranch')
      ->with($config[Config::MAIN_BRANCH], $runtime['current_branch']);
    $git->expects($this->never())->method('push');
    $git->expects($this->once())
      ->method('deleteBranch')
      ->with($runtime['current_branch']);
    $tests[] = [$config, $runtime, $git];

    //
    // Begin main & develop, minor change, should tag, no pushing.
    //
    $config = [
      Config::MAIN_BRANCH => 'main',
      Config::DEVELOP_BRANCH =>'develop',
      Config::PUSH_TAGS => FALSE,
      Config::PUSH_MASTER => FALSE,
      Config::PUSH_DEVELOP => FALSE,
    ];
    $runtime = [
      'master_version' => '0.1.5',
      'new_version' => '0.2.0',
      'current_branch' => 'release-0.2.0',
    ];
    $git = $this->createMock(GitProxy::class);
    $git->expects($this->once())->method('tag')->with($runtime['new_version']);
    $git->expects($this->exactly(2))
      ->method('mergeBranch')
      ->withConsecutive(
        [$config['develop'], $runtime['current_branch']],
        [$config[Config::MAIN_BRANCH], $runtime['current_branch']],
      );
    $git->expects($this->never())->method('push');
    $git->expects($this->once())
      ->method('deleteBranch')
      ->with($runtime['current_branch']);
    $tests[] = [$config, $runtime, $git];


    //
    // Begin main & develop, patch change, should tag, should push.
    //
    $config = [
      Config::MAIN_BRANCH => 'main',
      Config::DEVELOP_BRANCH =>'develop',
    ];
    $runtime = [
      'master_version' => '0.0.1',
      'new_version' => '0.0.2',
      'current_branch' => 'hotfix-0.0.2',
    ];
    $git = $this->createMock(GitProxy::class);
    $git->expects($this->once())->method('tag')->with($runtime['new_version']);
    $git->expects($this->exactly(2))
      ->method('mergeBranch')
      ->withConsecutive(
        [$config['develop'], $runtime['current_branch']],
        [$config[Config::MAIN_BRANCH], $runtime['current_branch']],
      );
    $git->expects($this->exactly(3))->method('push')->withConsecutive(
      [$runtime['new_version']],
      [$config['develop']],
      [$config[Config::MAIN_BRANCH]],
    );
    $git->expects($this->once())
      ->method('deleteBranch')
      ->with($runtime['current_branch']);
    $tests[] = [$config, $runtime, $git];

    //
    // Begin main only, patch change, should tag, should push.
    //
    $config = [
      Config::MAIN_BRANCH => 'main',
    ];
    $runtime = [
      'master_version' => '0.0.1',
      'new_version' => '0.0.2',
      'current_branch' => 'hotfix-0.0.2',
    ];
    $git = $this->createMock(GitProxy::class);
    $git->expects($this->once())->method('tag')->with($runtime['new_version']);
    $git->expects($this->once())
      ->method('mergeBranch')
      ->with($config[Config::MAIN_BRANCH], $runtime['current_branch']);
    $git->expects($this->exactly(2))->method('push')->withConsecutive(
      [$runtime['new_version']],
      [$config[Config::MAIN_BRANCH]],
    );
    $git->expects($this->once())
      ->method('deleteBranch')
      ->with($runtime['current_branch']);
    $tests[] = [$config, $runtime, $git];

    //
    // Begin release type, main & develop, minor change, merges both and tags.
    //
    $config = [
      Config::MAIN_BRANCH => 'main',
      Config::DEVELOP_BRANCH => 'develop',
      Config::PUSH_TAGS => FALSE,
      Config::PUSH_MASTER => FALSE,
      Config::PUSH_DEVELOP => FALSE,
    ];
    $runtime = [
      'master_version' => '1.1.0',
      'new_version' => '1.2.0',
      'current_branch' => 'release--1.2.0',
      'branch_type' => GitFlow::RELEASE,
    ];
    $git = $this->createMock(GitProxy::class);
    $git->expects($this->once())->method('tag')->with($runtime['new_version']);
    $git->expects($this->exactly(2))
      ->method('mergeBranch')
      ->withConsecutive(
        ['develop', $runtime['current_branch']],
        ['main', $runtime['current_branch']],
      );
    $git->expects($this->never())->method('push');
    $git->expects($this->once())
      ->method('deleteBranch')
      ->with($runtime['current_branch']);
    $tests['release type merges develop then main and tags'] = [$config, $runtime, $git];

    //
    // Begin feature type, merges develop only, never tags.
    //
    $config = [
      Config::MAIN_BRANCH => 'main',
      Config::DEVELOP_BRANCH => 'develop',
      Config::PUSH_DEVELOP => FALSE,
    ];
    $runtime = [
      'master_version' => '1.1.0',
      'new_version' => '1.2.0',
      'current_branch' => 'feature--foo',
      'branch_type' => GitFlow::FEATURE,
    ];
    $git = $this->createMock(GitProxy::class);
    $git->expects($this->never())->method('tag');
    $git->expects($this->once())
      ->method('mergeBranch')
      ->with('develop', $runtime['current_branch']);
    $git->expects($this->never())->method('push');
    $git->expects($this->once())
      ->method('deleteBranch')
      ->with($runtime['current_branch']);
    $tests['feature type merges develop only'] = [$config, $runtime, $git];

    //
    // Begin types that bump does not finish: nothing is merged or deleted.
    //
    foreach ([GitFlow::DEVELOP, GitFlow::MASTER, 'unknown'] as $branch_type) {
      $config = [
        Config::MAIN_BRANCH => 'main',
        Config::DEVELOP_BRANCH => 'develop',
      ];
      $runtime = [
        'master_version' => '1.1.0',
        'new_version' => '1.2.0',
        'current_branch' => 'some-branch',
        'branch_type' => $branch_type,
      ];
      $git = $this->createMock(GitProxy::class);
      $git->expects($this->never())->method('mergeBranch');
      $git->expects($this->never())->method('tag');
      $git->expects($this->never())->method('push');
      $git->expects($this->never())->method('deleteBranch');
      $tests["{$branch_type} type has an empty merge list"] = [$config, $runtime, $git];
    }

    //
    // Begin main branch is not among the merge targets, so no tag.
    //
    $config = [
      Config::MAIN_BRANCH => '',
      Config::DEVELOP_BRANCH => 'develop',
      Config::PUSH_DEVELOP => FALSE,
    ];
    $runtime = [
      'master_version' => '1.1.0',
      'new_version' => '1.2.0',
      'current_branch' => 'release--1.2.0',
      'branch_type' => GitFlow::RELEASE,
    ];
    $git = $this->createMock(GitProxy::class);
    $git->expects($this->never())->method('tag');
    $git->expects($this->once())
      ->method('mergeBranch')
      ->with('develop', $runtime['current_branch']);
    $git->expects($this->never())->method('push');
    $git->expects($this->once())
      ->method('deleteBranch')
      ->with($runtime['current_branch']);
    $tests['main is not a merge target'] = [$config, $runtime, $git];

    //
    // Begin uncommitted changes, confirmed: continues.
    //
    $config = [
      Config::MAIN_BRANCH => 'main',
      Config::PUSH_TAGS => FALSE,
      Config::PUSH_MASTER => FALSE,
    ];
    $runtime = [
      'master_version' => '0.1.5',
      'new_version' => '0.1.6',
      'current_branch' => 'release-0.1.6',
      'uncommitted' => [' M src/foo.php', '?? bar.txt'],
      'confirm' => TRUE,
    ];
    $git = $this->createMock(GitProxy::class);
    $git->expects($this->once())->method('tag')->with($runtime['new_version']);
    $git->expects($this->once())
      ->method('mergeBranch')
      ->with('main', $runtime['current_branch']);
    $git->expects($this->never())->method('push');
    $git->expects($this->once())
      ->method('deleteBranch')
      ->with($runtime['current_branch']);
    $tests['uncommitted changes, confirmed'] = [$config, $runtime, $git];

    //
    // Begin uncommitted changes, declined: cancelled before anything happens.
    //
    $config = [
      Config::MAIN_BRANCH => 'main',
    ];
    $runtime = [
      'master_version' => '0.1.5',
      'new_version' => '0.1.6',
      'current_branch' => 'release-0.1.6',
      'uncommitted' => [' M src/foo.php'],
      'confirm' => FALSE,
      'expected_status' => Command::FAILURE,
      'expected_output' => [' M src/foo.php', '<error>Cancelled!</error>'],
    ];
    $git = $this->createMock(GitProxy::class);
    $git->expects($this->never())->method('mergeBranch');
    $git->expects($this->never())->method('tag');
    $git->expects($this->never())->method('push');
    $git->expects($this->never())->method('deleteBranch');
    $tests['uncommitted changes, declined'] = [$config, $runtime, $git];

    //
    // Begin push develop on, push master off: only develop is pushed.
    //
    $config = [
      Config::MAIN_BRANCH => 'main',
      Config::DEVELOP_BRANCH => 'develop',
      Config::PUSH_TAGS => FALSE,
      Config::PUSH_DEVELOP => TRUE,
      Config::PUSH_MASTER => FALSE,
    ];
    $runtime = [
      'master_version' => '0.0.1',
      'new_version' => '0.0.2',
      'current_branch' => 'hotfix--0.0.2',
    ];
    $git = $this->createMock(GitProxy::class);
    $git->expects($this->once())->method('tag')->with($runtime['new_version']);
    $git->expects($this->exactly(2))->method('mergeBranch');
    $git->expects($this->once())->method('push')->with('develop');
    $git->expects($this->once())->method('deleteBranch');
    $tests['push develop only'] = [$config, $runtime, $git];

    //
    // Begin push develop off, push master on: only main is pushed.
    //
    $config = [
      Config::MAIN_BRANCH => 'main',
      Config::DEVELOP_BRANCH => 'develop',
      Config::PUSH_TAGS => FALSE,
      Config::PUSH_DEVELOP => FALSE,
      Config::PUSH_MASTER => TRUE,
    ];
    $git = $this->createMock(GitProxy::class);
    $git->expects($this->once())->method('tag')->with($runtime['new_version']);
    $git->expects($this->exactly(2))->method('mergeBranch');
    $git->expects($this->once())->method('push')->with('main');
    $git->expects($this->once())->method('deleteBranch');
    $tests['push master only'] = [$config, $runtime, $git];

    //
    // Begin the version carries a build number: the tag is the bare core.
    //
    $config = [
      Config::MAIN_BRANCH => 'main',
      Config::CREATE_TAGS => VersionDegree::MINOR,
    ];
    $runtime = [
      'master_version' => '0.1.5',
      'new_version' => '0.2.0+7',
      'current_branch' => 'release-0.2.0',
      'branch_type' => GitFlow::RELEASE,
    ];
    $git = $this->createMock(GitProxy::class);
    $git->expects($this->once())->method('tag')->with('0.2.0');
    $git->expects($this->once())
      ->method('mergeBranch')
      ->with('main', $runtime['current_branch']);
    $git->expects($this->exactly(2))->method('push')->withConsecutive(
      ['0.2.0'],
      ['main'],
    );
    $git->expects($this->once())
      ->method('deleteBranch')
      ->with($runtime['current_branch']);
    $tests['build number is stripped from the tag'] = [$config, $runtime, $git];

    return $tests;
  }

  /**
   * @dataProvider dataFortestDoneCommandProvider
   */
  public function testDoneCommand(array $config, array $runtime, GitProxy $git) {
    $git->method('getUncommittedFiles')->willReturn($runtime['uncommitted'] ?? []);
    $lines = [];
    $command = $this->createCommand($config, $runtime, $git);
    $status = $command->doExecute($this->createMock(InputInterface::class), $this->createOutput($lines));

    $this->assertSame($runtime['expected_status'] ?? Command::SUCCESS, $status);
    foreach ($runtime['expected_output'] ?? [] as $expected_line) {
      $this->assertContains($expected_line, $lines);
    }
  }

  public function testCreateBuildsTheCommandFromTheFourContainerServices() {
    $config = [
      Config::MAIN_BRANCH => 'main',
      Config::PUSH_TAGS => FALSE,
      Config::PUSH_MASTER => FALSE,
    ];
    $runtime = [
      'master_version' => '0.1.5',
      'new_version' => '0.1.6',
      'current_branch' => 'release-0.1.6',
    ];
    $git = $this->createMock(GitProxy::class);
    $git->expects($this->once())->method('tag')->with('0.1.6');
    $git->expects($this->once())->method('mergeBranch')->with('main', 'release-0.1.6');
    $git->expects($this->once())->method('deleteBranch')->with('release-0.1.6');

    $context = $this->createContext($runtime);
    $this->setPreviousVersion($context, $runtime['master_version']);
    $scribe = $this->createMock(VersionScribeInterface::class);
    $scribe->method('read')->willReturn($runtime['new_version']);
    $factory = $this->createConfiguredMock(VersionScribeFactory::class, ['__invoke' => $scribe]);

    // The container invokes a callable service when resolving it, so closures
    // are used to hand back the invokable mocks themselves.
    $loader = $this->createLoadConfigMock($config);
    $container = new Container();
    $container->add('config.loader', function () use ($loader) {
      return $loader;
    });
    $container->add('context', $context);
    $container->add('git', $git);
    $container->add('scribe.factory', function () use ($factory) {
      return $factory;
    });

    $command = Testable::create($container);
    $this->assertInstanceOf(DoneCommand::class, $command);
    $this->assertSame('done', $command->getName());
    $command->setHelperSet(new HelperSet(['question' => $this->createMock(QuestionHelper::class)]));
    $lines = [];
    $this->assertSame(Command::SUCCESS, $command->doExecute($this->createMock(InputInterface::class), $this->createOutput($lines)));
  }

  /**
   * The validators are bypassed when WEB_PACKAGE_ENV is "testing"; turn that
   * off and run from outside of any project to reach the violation return.
   */
  public function testDoneCommandFailsWhenNotInitialized() {
    $original_env = getenv('WEB_PACKAGE_ENV');
    $original_cwd = getcwd();
    $empty_dir = sys_get_temp_dir() . '/bump_done_' . uniqid('', TRUE);
    mkdir($empty_dir);
    try {
      putenv('WEB_PACKAGE_ENV');
      chdir($empty_dir);

      $git = $this->createMock(GitProxy::class);
      $git->expects($this->never())->method('mergeBranch');
      $git->expects($this->never())->method('tag');
      $git->expects($this->never())->method('deleteBranch');
      $runtime = [
        'master_version' => '0.1.5',
        'new_version' => '0.1.6',
        'current_branch' => 'release-0.1.6',
      ];
      $lines = [];
      $command = $this->createCommand([Config::MAIN_BRANCH => 'main'], $runtime, $git);
      $status = $command->doExecute($this->createMock(InputInterface::class), $this->createOutput($lines));

      $this->assertSame(Command::FAILURE, $status);
      $this->assertNotEmpty($lines);
    }
    finally {
      chdir($original_cwd);
      rmdir($empty_dir);
      putenv(FALSE === $original_env ? 'WEB_PACKAGE_ENV' : 'WEB_PACKAGE_ENV=' . $original_env);
    }
  }

  private function createContext(array $runtime): Context {
    return $this->createConfiguredMock(Context::class, [
      'getCurrentBranch' => $runtime['current_branch'],
      'getBranchType' => $runtime['branch_type'] ?? GitFlow::HOTFIX,
      'getRootPath' => dirname($this->getPath('')),
    ]);
  }

  /**
   * @param array $lines
   *   Receives every line written with writeln().
   */
  private function createOutput(array &$lines): OutputInterface {
    $output = $this->createMock(OutputInterface::class);
    $output->method('writeln')->willReturnCallback(function ($messages) use (&$lines) {
      $lines = array_merge($lines, (array) $messages);
    });

    return $output;
  }

  private function createCommand(array $config, array $runtime, GitProxy $git): Testable {
    $context = $this->createContext($runtime);
    $this->setPreviousVersion($context, $runtime['master_version']);

    $scribe = $this->createMock(VersionScribeInterface::class);
    $scribe->method('read')->willReturn($runtime['new_version']);

    $command = new Testable($this->createLoadConfigMock($config)(), $context, $git, $scribe);
    $helper = $this->createMock(QuestionHelper::class);
    $helper->method('ask')->willReturn($runtime['confirm'] ?? TRUE);
    $command->setHelperSet(new HelperSet(['question' => $helper]));

    return $command;
  }

}

class Testable extends DoneCommand {

  public function doExecute($input, $output): int {
    return $this->execute($input, $output);
  }
}
