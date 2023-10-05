<?php

namespace AKlump\WebPackage\Command;

use AKlump\WebPackage\Helpers\GetCurrentVersion;
use AKlump\WebPackage\Helpers\GetHookEvent;
use AKlump\WebPackage\Hooks\HookManager;
use AKlump\WebPackage\Traits\HasConfigTrait;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class BaseHooksCommand extends Command {

  use HasConfigTrait;

  protected $scribe;

  /**
   * @var string
   */
  protected $hookType;

  public function __construct(ContainerInterface $container) {
    $this->setHookType(static::$defaultName);
    $this->container = $container;
    // This must come last!
    parent::__construct();
  }

  public function getHookType(): string {
    return $this->hookType;
  }

  public function setHookType(string $hookType): self {
    $this->hookType = $hookType;

    return $this;
  }

  protected function configure() {
    $hook_type = $this->getHookType();
    $this
      ->setDescription("Run $hook_type-type hooks without affecting versioning.")
      ->addArgument('filter', InputArgument::OPTIONAL, 'Limit which hooks run. Maybe be substring or glob pattern.')
      ->setHelp("The filter works as a substring, so given these $hook_type hooks:\n\nipsum.php\nlorem.php\nlorem.sh\n\nHere are filter examples:\n\nbump $hook_type lor -> (lorem.php, lorem.sh)\nbump $hook_type .php -> (ipsum.php, lorem.php)\n\nHooks are executed in alphabetical order. To control order use double-digit prefixes:\n\n00_lorem.sh\n00_lorem.php\n10_ipsum.php");
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $this->setConfig($this->container->get('config.loader')());
    $config = $this->getConfig();
    $event = (new GetHookEvent($config))();
    $version = (new GetCurrentVersion($config, $this->container->get('scribe.factory')()))();
    $event->setPreviousVersion($version);
    $event->setVersion($version);
    $filter = $input->getArgument('filter') ?? '';
    (new HookManager($output, $event))->run($this->getHookType(), $filter);

    return Command::SUCCESS;
  }

}
