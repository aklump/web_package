<?php

namespace AKlump\WebPackage\Command;

use AKlump\WebPackage\Config\GetVersionScribe;
use AKlump\WebPackage\Config\LoadConfig;
use AKlump\WebPackage\Helpers\GetBranchType;
use AKlump\WebPackage\Helpers\GetCurrentBranch;
use AKlump\WebPackage\Model\GitFlow;
use AKlump\WebPackage\Traits\ImplodeTrait;
use AKlump\WebPackage\Traits\ShellCommandTrait;
use AKlump\WebPackage\Traits\ValidationTrait;
use AKlump\WebPackage\Validator\Constraint\GitBranch;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @url https://nvie.com/posts/a-successful-git-branching-model/
 */
class DoneCommand extends Command {

  use ValidationTrait;
  use ShellCommandTrait;
  use ImplodeTrait;

  protected static $defaultName = 'done';

  protected function configure() {
    $this
      ->setDescription('Finish a feature, release or hotfix.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->output = $output;
    $config = (new LoadConfig())();

    $branch_to_merge = (new GetCurrentBranch())();
    $typer = (new GetBranchType($config));
    $branch_type = $typer($branch_to_merge);
    $gitflow = new Gitflow($branch_type, $config['master'], $config['develop']);
    $branch_types = array_map(function ($name) use ($typer) {
      return $typer($name);
    }, $gitflow->getMayFinish());
    $this->validate($branch_type, [
      new GitBranch(
        $branch_types,
        sprintf('You may only finish "%s" branches.  Switch branches and try again.', $this->and($branch_types)),
      ),
    ]);

    if ($this->handleViolations() > 0) {
      return Command::FAILURE;
    }

    $merge_into_branches = $gitflow->getMustMergeBackInto();
    $merge_successful = FALSE;
    foreach ($merge_into_branches as $target_branch) {
      $command = [];
      $this->system(sprintf('git checkout %s', $target_branch));
      $this->system(sprintf('git merge --no-ff %s -m "Merge branch %s"', $branch_to_merge, $branch_to_merge));
      $output->writeln(sprintf('<info>Successfully merged %s into %s.', $branch_to_merge, $target_branch));
      $output->write($command);
      $merge_successful = TRUE;
    }
    if ($merge_successful
      // Make sure the branch type is one that has updated a version.
      && in_array($branch_type, [GitFlow::HOTFIX, GitFlow::RELEASE])
      // We only do this once, when we're processing master.
      && in_array($config['master'], $merge_into_branches)) {
      $version = (new GetVersionScribe($config))()->read();
      $this->system(sprintf('git tag %s', $version));
    }
    if ($merge_successful) {
      $this->system(sprintf('git branch -d %s', $branch_to_merge));
    }

    return Command::SUCCESS;
  }

}
