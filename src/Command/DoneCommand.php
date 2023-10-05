<?php

namespace AKlump\WebPackage\Command;

use AKlump\WebPackage\Config\Config;
use AKlump\WebPackage\Config\ConfigDefaults;
use AKlump\WebPackage\Git\GitProxy;
use AKlump\WebPackage\Helpers\GetCurrentVersion;
use AKlump\WebPackage\Helpers\GetPreviousVersion;
use AKlump\WebPackage\Helpers\ShouldCreateTag;
use AKlump\WebPackage\Model\Context;
use AKlump\WebPackage\Model\GitFlow;
use AKlump\WebPackage\Traits\HasContextTrait;
use AKlump\WebPackage\Traits\ImplodeTrait;
use AKlump\WebPackage\Traits\ShellCommandTrait;
use AKlump\WebPackage\Traits\ValidationTrait;
use AKlump\WebPackage\Validator\Constraint\GitBranch;
use AKlump\WebPackage\Validator\Constraint\IsInitialized;
use AKlump\WebPackage\VersionScribeInterface;
use Psr\Container\ContainerInterface;
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
  use HasContextTrait;

  protected static $defaultName = 'done';

  private $git;

  /** @var array */
  private $config;

  /** @var \AKlump\WebPackage\Model\Context */
  private $context;

  /**
   * @var \AKlump\WebPackage\VersionScribeInterface|null
   */
  private $scribe;

  protected function configure() {
    $this
      ->setDescription('Finish a feature, release or hotfix.');
  }

  public function __construct(array $config, Context $context, GitProxy $git, ?VersionScribeInterface $scribe) {
    parent::__construct();
    $this->config = $config + [
        Config::MAIN_BRANCH => ConfigDefaults::MAIN_BRANCH,
        Config::DEVELOP_BRANCH => ConfigDefaults::DEVELOP_BRANCH,
      ];
    $this->setContext($context);
    $this->git = $git;
    $this->scribe = $scribe;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.loader')(),
      $container->get('context'),
      $container->get('git'),
      $container->get('scribe.factory')(),
    );
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $this->output = $output;
    $this->validate(getcwd(), [new IsInitialized()]);

    $branch_to_merge = $this->context->getCurrentBranch();
    $branch_type = $this->context->getBranchType($branch_to_merge);
    $gitflow = new Gitflow($branch_type, $this->config[Config::MAIN_BRANCH], $this->config[Config::DEVELOP_BRANCH]);
    $branch_types = array_map(function ($name) {
      return $this->context->getBranchType($name);
    }, $gitflow->getMayFinish());
    $branch_types = array_unique($branch_types);

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
      $this->git->mergeBranch($target_branch, $branch_to_merge);
      $output->writeln(sprintf('<info>Successfully merged %s into %s.', $branch_to_merge, $target_branch));
      $output->write($command);
      $merge_successful = TRUE;
    }

    $old_version = (new GetPreviousVersion($this->getContext()))();
    $new_version = (new GetCurrentVersion($this->config, $this->scribe))();

    // Create a tag?
    if ($merge_successful
      // Make sure the branch type is one that has updated a version.
      && in_array($branch_type, [GitFlow::HOTFIX, GitFlow::RELEASE])
      // We only do this once, when we're processing master.
      && in_array($this->config[Config::MAIN_BRANCH], $merge_into_branches)
      && (new ShouldCreateTag($this->config))($old_version, $new_version)
    ) {
      $this->git->tag($new_version);

      if ($this->config[Config::PUSH_TAGS] ?? ConfigDefaults::PUSH_TAGS) {
        $this->git->push($new_version);
      }
    }

    if ($merge_successful) {
      foreach ($merge_into_branches as $target_branch) {
        if (
          ($this->config[Config::DEVELOP_BRANCH] === $target_branch && ($this->config[Config::PUSH_DEVELOP] ?? ConfigDefaults::PUSH_DEVELOP))
          || ($this->config[Config::MAIN_BRANCH] === $target_branch && ($this->config[Config::PUSH_MASTER] ?? ConfigDefaults::PUSH_MASTER))
        ) {
          $this->git->push($target_branch);
        }
      }
    }

    if ($merge_successful) {
      $this->git->deleteBranch($branch_to_merge);
    }

    return Command::SUCCESS;
  }

}
