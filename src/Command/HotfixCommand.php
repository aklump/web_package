<?php

namespace AKlump\WebPackage\Command;

use AKlump\WebPackage\Config\Config;
use AKlump\WebPackage\Config\ConfigDefaults;
use AKlump\WebPackage\Config\DefaultConfig;
use AKlump\WebPackage\Helpers\GetCurrentBranch;
use AKlump\WebPackage\Helpers\GetHookEvent;
use AKlump\WebPackage\Helpers\GetPreviousVersion;
use AKlump\WebPackage\Helpers\Stash;
use AKlump\WebPackage\Model\GitFlow;
use AKlump\WebPackage\Validator\Constraint\GitBranch;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AKlump\WebPackage\Model\Version;

/**
 * @url https://nvie.com/posts/a-successful-git-branching-model/#hotfix-branches
 */
class HotfixCommand extends BaseBranchCommand {

  protected static $defaultName = 'hotfix';

  protected function configure() {
    $this->setDescription('Create a new hotfix branch per Gitflow.')
      ->setAliases(['hf']);
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $this->output = $output;

    $gitflow = new Gitflow(GitFlow::HOTFIX, $this->config['master'], $this->config['develop']);
    $starting_branch = (new GetCurrentBranch())();
    $branches = $gitflow->getMayBranchOffFrom();
    $this->validate($starting_branch, [
      new GitBranch(
        $branches,
        sprintf("To create a hotfix branch, switch to \"%s\" and try again.\nOr set your master/main branch to \"%s\" in the configuration.", $this->or($branches), $starting_branch),
      ),
    ]);
    if ($this->handleViolations() > 0) {
      return Command::FAILURE;
    }

    $version = $this->scribe->read();
    (new Stash($this->context))->write(GetPreviousVersion::STASH_KEY, $version);

    $event = (new GetHookEvent($this->config))();
    $event->setPreviousVersion($version);

    $semver = Version::parse($version);
    $new_version = $semver->getNextPatchVersion();
    $event->setVersion((string) $new_version);

    $this->git->checkoutBranch($gitflow->getBranchName($event->getVersion()), $starting_branch);
    $this->scribe->write($new_version);


    if ($this->config[Config::DO_VERSION_COMMIT] ?? ConfigDefaults::DO_VERSION_COMMIT) {
      $version_file = $this->scribe->getFilepath();
      if ($version_file) {
        $this->git->commitFile($version_file, sprintf('Bumped version number to %s', $event->getVersion()));
      }
    }

    return Command::SUCCESS;
  }

}
