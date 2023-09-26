<?php

namespace AKlump\WebPackage\Command;

use AKlump\WebPackage\Access\IsInitialized;
use AKlump\WebPackage\UpgradeException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VersionCommand extends Command {

  protected static $defaultName = 'version';

  protected function configure() {
    $this
      ->setAliases(['v'])
      ->setDescription("Display your app's version.");
  }

  public function __construct(ContainerInterface $container) {
    parent::__construct();
    $this->container = $container;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    if (!(new IsInitialized())->access()) {
      throw new \RuntimeException("It seems you are outside your app.");
    }

    $scribe = $this->container->get('scribe.factory')();
    if (!$scribe) {
      throw new UpgradeException();
    }
    $output->writeln((string) $scribe->read());

    return Command::SUCCESS;
  }

}
