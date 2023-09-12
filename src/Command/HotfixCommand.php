<?php

namespace AKlump\WebPackage\Command;

use AKlump\WebPackage\Config\GetVersionScribe;
use AKlump\WebPackage\Config\LoadConfig;
use AKlump\WebPackage\Helpers\GetCurrentBranch;
use AKlump\WebPackage\Helpers\GetHookEvent;
use AKlump\WebPackage\Model\GitFlow;
use AKlump\WebPackage\Traits\ImplodeTrait;
use AKlump\WebPackage\Traits\ShellCommandTrait;
use AKlump\WebPackage\Traits\ValidationTrait;
use AKlump\WebPackage\Validator\Constraint\GitBranch;
use AKlump\WebPackage\Validator\Constraint\VersionDegree;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use AKlump\WebPackage\Model\Version;

/**
 * @url https://nvie.com/posts/a-successful-git-branching-model/#hotfix-branches
 */
class HotfixCommand extends Command {

  use ValidationTrait;
  use ShellCommandTrait;
  use ImplodeTrait;

  protected static $defaultName = 'hotfix';

  protected function configure() {
    $this->setDescription('Create a new release branch per Gitflow.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->output = $output;

    $config = (new LoadConfig())();
    $gitflow = new Gitflow(GitFlow::HOTFIX, $config['master'], $config['develop']);
    $starting_branch = (new GetCurrentBranch())();
    $branches = $gitflow->getMayBranchOffFrom();
    $this->validate($starting_branch, [
      new GitBranch(
        $branches,
        sprintf('To create a hotfix branch, switch to "%s" and try again.', $this->or($branches)),
      ),
    ]);
    if ($this->handleViolations() > 0) {
      return Command::FAILURE;
    }

    $version_scribe = (new GetVersionScribe($config))();
    $version = $version_scribe->read();

    $event = (new GetHookEvent($config))();
    $event->setPreviousVersion($version);

    $semver = Version::parse($version);
    $new_version = $semver->getNextPatchVersion();
    $event->setVersion((string) $new_version);

    $this->system(sprintf('git checkout -b %s %s', $gitflow->getBranchName($event->getVersion()), $starting_branch));
    $version_scribe->write($new_version);

    if ($config['do_version_commit']) {
      $version_file = $version_scribe->getFilepath();
      if ($version_file) {
        $this->system(sprintf('git add %s', $version_file));
      }
      $this->system(sprintf('git commit -a -m "Bumped version number to %s"', $event->getVersion()));
    }

    return Command::SUCCESS;
  }

}
