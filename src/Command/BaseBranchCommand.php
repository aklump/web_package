<?php

namespace AKlump\WebPackage\Command;

use AKlump\WebPackage\Git\GitProxy;
use AKlump\WebPackage\Helpers\GetCurrentBranch;
use AKlump\WebPackage\Helpers\GetHookEvent;
use AKlump\WebPackage\Helpers\GetPreviousVersion;
use AKlump\WebPackage\Helpers\Stash;
use AKlump\WebPackage\Model\Context;
use AKlump\WebPackage\Model\GitFlow;
use AKlump\WebPackage\Traits\HasConfigTrait;
use AKlump\WebPackage\Traits\HasContainerTrait;
use AKlump\WebPackage\Traits\HasContextTrait;
use AKlump\WebPackage\Traits\ImplodeTrait;
use AKlump\WebPackage\Traits\ShellCommandTrait;
use AKlump\WebPackage\Traits\ValidationTrait;
use AKlump\WebPackage\Validator\Constraint\GitBranch;
use AKlump\WebPackage\VersionScribeInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AKlump\WebPackage\Model\Version;

/**
 * @url https://nvie.com/posts/a-successful-git-branching-model/#hotfix-branches
 */
abstract class BaseBranchCommand extends Command {

  use ValidationTrait;
  use ShellCommandTrait;
  use ImplodeTrait;

  protected static $defaultName = 'hotfix';

  /**
   * @var array
   */
  protected $config;

  /**
   * @var \AKlump\WebPackage\Git\GitProxy
   */
  protected $git;

  /**
   * @var \AKlump\WebPackage\VersionScribeInterface
   */
  protected $scribe;

  /**
   * @var \AKlump\WebPackage\Model\Context
   */
  protected $context;

  /**
   * @var \Symfony\Component\Console\Output\OutputInterface
   */
  protected $output;

  public function __construct($config, Context $context, VersionScribeInterface $scribe, GitProxy $git) {
    parent::__construct();
    $this->config = $config;
    $this->context = $context;
    $this->scribe = $scribe;
    $this->git = $git;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.loader')(),
      $container->get('context'),
      $container->get('scribe.factory')(),
      $container->get('git'),
    );
  }

}
