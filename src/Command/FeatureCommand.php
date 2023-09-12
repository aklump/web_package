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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @url https://nvie.com/posts/a-successful-git-branching-model/#feature-branches
 */
class FeatureCommand extends Command {

  use ValidationTrait;
  use ShellCommandTrait;
  use ImplodeTrait;

  protected static $defaultName = 'feature';

  protected function configure() {
    $this
      ->setDescription('Create a new feature branch per Gitflow.')
      ->addArgument('name', InputArgument::REQUIRED, 'The name of the feature.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->output = $output;
    $name = $input->getArgument('name');
    $config = (new LoadConfig())();
    $gitflow = new Gitflow(GitFlow::FEATURE, $config['master'], $config['develop']);
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

    $version_scribe = (new GetVersionScribe($config))();
    $version = $version_scribe->read();

    $event = (new GetHookEvent($config))();
    $event->setPreviousVersion($version);
    $event->setVersion($version);

    $branch_name = $gitflow->getBranchName($name);
    $this->system(sprintf('git checkout -b %s %s', $branch_name, $starting_branch));

    return Command::SUCCESS;
  }

}
