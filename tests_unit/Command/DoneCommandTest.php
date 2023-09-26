<?php

namespace AKlump\WebPackage\Tests\Command;

use AKlump\WebPackage\Command\DoneCommand;
use AKlump\WebPackage\Config\Config;
use AKlump\WebPackage\Git\GitProxy;
use AKlump\WebPackage\Helpers\VersionDegree;
use AKlump\WebPackage\Model\Context;
use AKlump\WebPackage\Model\GitFlow;
use AKlump\WebPackage\Tests\TestingTraits\TestWithConfigTrait;
use AKlump\WebPackage\Tests\WriteTestTrait;
use AKlump\WebPackage\VersionScribeInterface;
use League\Container\Container;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers \AKlump\WebPackage\Command\DoneCommand
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

    return $tests;
  }

  /**
   * @dataProvider dataFortestDoneCommandProvider
   */
  public function testDoneCommand(array $config, array $runtime, GitProxy $git) {
    $container = new Container();
    $config_loader = $this->createLoadConfigMock($config);
    $container->add('config.loader', $config_loader);
    $context = $this->createConfiguredMock(Context::class, [
      'getCurrentBranch' => $runtime['current_branch'],
      'getBranchType' => GitFlow::HOTFIX,
      'getRootPath' => dirname($this->getPath('')),
    ]);
    $container->add('context', $context);
    $container->add('git', $git);

    $this->setPreviousVersion($context, $runtime['master_version']);

    $input = $this->createMock(InputInterface::class);
    $output = $this->createMock(OutputInterface::class);

    $scribe = $this->createMock(VersionScribeInterface::class);
    $scribe->method('read')->willReturn($runtime['new_version']);

    $command = new Testable($config_loader(), $context, $git, $scribe);
    $this->assertSame(Command::SUCCESS, $command->doExecute($input, $output));
  }

}

class Testable extends DoneCommand {

  public function doExecute($input, $output): int {
    return $this->execute($input, $output);
  }
}
