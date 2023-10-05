<?php

namespace AKlump\WebPackage\Command;

use AKlump\WebPackage\Helpers\GetCurrentVersion;
use AKlump\WebPackage\Traits\HasConfigTrait;
use AKlump\WebPackage\Traits\ValidationTrait;
use AKlump\WebPackage\Validator\Constraint\IsInitialized;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AKlump\WebPackage\Model\Version;

abstract class BaseVersionCommand extends Command {

  use ValidationTrait;
  use HasConfigTrait;

  protected $container;

  public function __construct(ContainerInterface $container) {
    parent::__construct();
    $this->container = $container;
    $this->setConfig($container->get('config.loader')());
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $this->validate(getcwd(), [new IsInitialized()]);
    if ($this->handleViolations() > 0) {
      return Command::FAILURE;
    }

    $scribe = $this->container->get('scribe.factory')();
    $version = (new GetCurrentVersion($this->getConfig(), $scribe))();
    $version = Version::parse($version);
    $next_version = $this->getNextVersion($version);
    $scribe->write($next_version);
    $output->writeln(sprintf('<info>Version bumped from %s to %s</info>', $version, $next_version));

    return Command::SUCCESS;
  }

  abstract protected function getNextVersion(Version $version): Version;

}
