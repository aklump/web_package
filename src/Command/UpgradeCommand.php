<?php

namespace AKlump\WebPackage\Command;

use AKlump\WebPackage\Config\Config;
use AKlump\WebPackage\Config\ConfigDefaults;
use AKlump\WebPackage\Traits\HasConfigTrait;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpgradeCommand extends Command {

  use HasConfigTrait;

  const REMOVED_KEYS = ['pause'];

  protected static $defaultName = 'upgrade';

  private $config;

  /**
   * @var int
   */
  private $status;

  protected function configure() {
    $this->setDescription("Display helpful upgrade information.");
  }

  public function __construct(ContainerInterface $container) {
    parent::__construct();
    $this->setConfig($container->get('config.loader')());
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $this->output = $output;
    $this->status = Command::SUCCESS;
    $this->handleInfoFile();
    $this->handleSteps();
    $this->handlePause();

    return $this->status;
  }

  private function handleInfoFile() {
    if (array_key_exists('info_file', $this->config)) {
      $this->status = Command::FAILURE;
      $this->output->writeLn('<error>Configuration "info_file" should be "version_file".  Edit your configuration and try again.</error>');
    }

    $version_file = $this->config['info_file'] ?? $this->config[Config::VERSION_FILE] ?? ConfigDefaults::VERSION_FILE;
    if ('composer.json' === basename($version_file)) {
      $this->output->writeln([
        // TODO A better dynamic suggestion based on the install dir.
        '<error>Not recommended to use composer.json#version; how about .web_package/config.yml? (https://getcomposer.org/doc/04-schema.md#version)',
      ]);
    }
  }
  private function handlePause() {
    if (array_key_exists('pause', $this->config)) {
      $this->status = Command::FAILURE;
      $this->output->writeLn('<error>Configuration "pause" should be removed.</error>');
    }
  }

  private function handleSteps() {
    foreach (['major_step', 'minor_step', 'patch_step'] as $key) {
      if (array_key_exists($key, $this->config)) {
        $this->output->writeLn("<error>$key is no longer used and should be removed.</error>");
      }
    }
  }

}
