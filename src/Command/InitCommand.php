<?php

namespace AKlump\WebPackage\Command;

use AKlump\WebPackage\Access\IsInitialized;
use AKlump\WebPackage\Config\ConfigManager;
use AKlump\WebPackage\Config\LoadConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Yaml\Yaml;

class InitCommand extends Command {

  protected static $defaultName = 'init';

  /**
   * @var string
   */
  private $dir;

  /**
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  private $filesystem;

  protected function configure() {
    $this->setDescription('Initialize the current directory.')
      ->addArgument('template', InputArgument::OPTIONAL)
      ->setHelp('This command allows you to see all available versions to be swapped');
  }

  public function __construct(string $installation_dir, string $template_dir, $filesystem) {
    parent::__construct('init');
    if (!Path::isAbsolute($installation_dir)) {
      $installation_dir = getcwd() . "/$installation_dir";
    }
    $this->dir = $installation_dir;
    $this->templateDir = $template_dir;
    $this->filesystem = $filesystem;
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    try {
      if ((new IsInitialized(getcwd()))) {
        throw new \RuntimeException(sprintf("%s is already initialized.", $this->dir));
      }

      $template = $input->getArgument('template');
      if ($template) {
        $path_to_template = $this->tryGetTemplatePath($template);
        if ($path_to_template) {
          $output->writeln(sprintf('<info>Template "%s" loaded.</info>', $template));
        }
      }

      // Copy over from the installation template.
      $command = sprintf('rsync -a %s/ %s/ --exclude=tests', $this->templateDir, $this->dir);
      exec($command, $exec_echo, $status);
      if (0 !== $status) {
        throw new \RuntimeException(implode(PHP_EOL, $exec_echo));
      }
      if ($this->filesystem->exists($this->dir . '/gitignore')) {
        $this->filesystem->rename($this->dir . '/gitignore', $this->dir . '/.gitignore');
      }

      $destination = (new ConfigManager())->locateFile($this->dir . '/config');
      $extension = strtolower(Path::getExtension($destination));
      if (!in_array($extension, ['yml', 'yaml'])) {
        throw new \InvalidArgumentException('Unsupported config file type: %s.', $extension);
      }

      $config = (new LoadConfig())($template);
      if ($template) {
        $config = array_merge(['template' => $template], $config);
      }

      $contents = Yaml::dump($config, 6);
      if (!file_put_contents($destination, $contents)) {
        throw new \RuntimeException(sprintf('Could not save to: %s', $destination));
      }

      $output->writeln('<info>Created ./.web_package directory</info>');
    }
    catch (\Exception $exception) {
      $output->writeln(sprintf("<error>%s</error>", $exception->getMessage()));

      return Command::FAILURE;
    }

    return Command::SUCCESS;
  }

  private function tryGetTemplatePath(string $template): string {
    $path_to_template = ConfigManager::getServerHome() . '/.web_package/config_' . $template;
    if (!$this->filesystem->exists($path_to_template)) {
      throw new \InvalidArgumentException(sprintf('Template "%s" not found at %s ', $template, $path_to_template));
    }

    return $path_to_template;
  }

}
