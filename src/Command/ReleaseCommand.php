<?php

namespace AKlump\WebPackage\Command;

use AKlump\WebPackage\Config\Config;
use AKlump\WebPackage\Config\ConfigDefaults;
use AKlump\WebPackage\Config\DefaultConfig;
use AKlump\WebPackage\Helpers\GetCurrentBranch;
use AKlump\WebPackage\Helpers\GetHookEvent;
use AKlump\WebPackage\Helpers\VersionDegree;
use AKlump\WebPackage\Model\GitFlow;
use AKlump\WebPackage\Validator\Constraint\GitBranch;
use AKlump\WebPackage\Validator\Constraint\VersionDegree as VersionDegreeConstraint;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use AKlump\WebPackage\Model\Version;

/**
 * @url https://nvie.com/posts/a-successful-git-branching-model/
 */
class ReleaseCommand extends BaseBranchCommand {

  protected static $defaultName = 'release';

  protected function configure() {
    $this
      ->setAliases(['r'])
      ->setDescription('Create a new release branch per Gitflow.')
      ->addArgument('degree', InputArgument::OPTIONAL, "Version degree change, one of major, minor, or patch.", VersionDegree::PATCH);
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    $this->output = $output;

    $version_degree = $input->getArgument('degree');
    $this->validate($version_degree, [
      new NotBlank(),
      new VersionDegreeConstraint([
        VersionDegree::MAJOR,
        VersionDegree::MINOR,
        VersionDegree::PATCH,
      ]),
    ]);

    $gitflow = new Gitflow(GitFlow::RELEASE, $this->config['master'], $this->config['develop']);
    $starting_branch = (new GetCurrentBranch())();
    $branches = $gitflow->getMayBranchOffFrom();
    $this->validate($starting_branch, [
      new GitBranch(
        $branches,
        sprintf('To create a release branch, switch to "%s" and try again.', $this->or($branches)),
      ),
    ]);

    if ($this->handleViolations() > 0) {
      return Command::FAILURE;
    }

    $version = $this->scribe->read();

    $event = (new GetHookEvent($this->config))();
    $event->setPreviousVersion($version);

    $semver = Version::parse($version);
    $change = [
      VersionDegree::MAJOR => $semver->getNextMajorVersion(),
      VersionDegree::MINOR => $semver->getNextMinorVersion(),
      VersionDegree::PATCH => $semver->getNextPatchVersion(),
    ];

    $new_version = $change[$version_degree];
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
