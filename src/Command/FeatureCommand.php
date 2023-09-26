<?php

namespace AKlump\WebPackage\Command;

use AKlump\WebPackage\Helpers\GetCurrentBranch;
use AKlump\WebPackage\Helpers\GetHookEvent;
use AKlump\WebPackage\Model\GitFlow;
use AKlump\WebPackage\Validator\Constraint\GitBranch;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @url https://nvie.com/posts/a-successful-git-branching-model/#feature-branches
 */
class FeatureCommand extends BaseBranchCommand {

  protected static $defaultName = 'feature';

  protected function configure() {
    $this
      ->setAliases(['f'])
      ->setDescription('Create a new feature branch per Gitflow.')
      ->addArgument('name', InputArgument::REQUIRED, 'The name of the feature.');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $this->output = $output;
    $name = $input->getArgument('name');

    $gitflow = new Gitflow(GitFlow::FEATURE, $this->config[Config::MAIN_BRANCH], $this->config[Config::DEVELOP_BRANCH]);
    $starting_branch = (new GetCurrentBranch())();
    $branches = $gitflow->getMayBranchOffFrom();
    $this->validate($starting_branch, [
      new GitBranch(
        $branches,
        sprintf('To create a feature branch, switch to "%s" and try again.', $this->or($branches)),
      ),
    ]);

    if ($this->handleViolations() > 0) {
      return Command::FAILURE;
    }

    $version = $this->scribe->read();

    $event = (new GetHookEvent($this->config))();
    $event->setPreviousVersion($version);
    $event->setVersion($version);

    $branch_name = $gitflow->getBranchName($name);
    $this->git->checkoutBranch($branch_name, $starting_branch);

    return Command::SUCCESS;
  }

}
