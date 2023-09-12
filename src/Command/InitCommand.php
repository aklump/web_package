<?php

namespace AKlump\WebPackage\Command;

use AKlump\WebPackage\Access\IsInitialized;
use AKlump\WebPackage\Config\ConfigManager;
use AKlump\WebPackage\Config\LoadConfig;
use AKlump\WebPackage\Helpers\GetAllBranches;
use AKlump\WebPackage\Helpers\GetAllTemplates;
use AKlump\WebPackage\Helpers\GetRootPath;
use AKlump\WebPackage\Helpers\GetServerHome;
use AKlump\WebPackage\Model\GitFlow;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
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
    $this->input = $input;
    $this->output = $output;

    try {
      if ((new IsInitialized())->access()) {
        $root = (new GetRootPath())();
        if (getcwd() !== $root) {
          throw new \RuntimeException(sprintf("You are within an initialized directory: %s", $root));
        }
        else {
          throw new \RuntimeException(sprintf("%s is already initialized.", $root));
        }
      }

      $template = $this->getTemplate();
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

      // This next step will sniff the environment if git is already used and/or
      // ask the user for input.
      $config['master'] = $this->getMainBranch($config['master']);
      $config['develop'] = $this->getDevelopBranch($config['develop']) ?? $config['master'];

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
    if (GetAllTemplates::DEFAULT === $template) {
      $path_to_template = (new GetServerHome())() . '/.web_package/config';
    }
    else {
      $path_to_template = (new GetServerHome())() . '/.web_package/config_' . $template;
    }
    if (!$this->filesystem->exists($path_to_template)) {
      throw new \InvalidArgumentException(sprintf('Template "%s" not found at %s ', $template, $path_to_template));
    }

    return $path_to_template;
  }

  private function getMainBranch(string $fallback): string {
    $branches = (new GetAllBranches())();
    $existing = array_values(array_filter($branches, function ($name) use ($fallback) {
      return in_array($name, ['main', GitFlow::MASTER, $fallback]);
    }))[0] ?? NULL;
    if ($existing) {
      return $existing;
    }

    $helper = $this->getHelper('question');
    $question = new Question('Main branch name? ');
    $question->setAutocompleterValues([
      GitFlow::MASTER,
      'main',
      'master',
      $fallback,
    ]);
    $name = $helper->ask($this->input, $this->output, $question);

    return $name ?: $fallback;
  }

  private function getDevelopBranch(string $fallback): ?string {
    $branches = (new GetAllBranches())();
    $existing = array_values(array_filter($branches, function ($name) use ($fallback) {
      if ($fallback === $name || GitFlow::DEVELOP === $name) {
        return TRUE;
      }

      return strstr($name, 'dev');
    }))[0] ?? NULL;
    if ($existing) {
      return $existing;
    }

    $helper = $this->getHelper('question');
    $question = new Question('Will you use a "develop" branch? ', 'no');
    $question->setAutocompleterValues(['yes', 'no']);
    $question->setNormalizer(function ($value) {
      $value = strtolower($value);
      if (substr($value, 0, 1) === 'y') {
        return 'yes';
      }
      if (substr($value, 0, 1) === 'n') {
        return 'no';
      }

      return $value;
    });
    $question->setValidator(function ($answer) {
      if (!in_array($answer, ['yes', 'no'])) {
        throw new \RuntimeException('Answer must be "yes" or "no"');
      }

      return strtolower($answer);
    });

    $will_have_develop = $helper->ask($this->input, $this->output, $question);
    if ('no' === $will_have_develop) {
      return NULL;
    }

    $question = new Question('Branch name? ');
    $question->setAutocompleterValues([GitFlow::DEVELOP]);
    $name = $helper->ask($this->input, $this->output, $question);

    return $name ?: NULL;
  }

  public function getTemplate(): ?string {
    $choices = array_keys((new GetAllTemplates())());
    $do_not_use = 'NONE';

    array_unshift($choices, $do_not_use);
    $helper = $this->getHelper('question');
    $question = new ChoiceQuestion("Use a template?", $choices);
    $template = $helper->ask($this->input, $this->output, $question);
    if ($template === $do_not_use) {
      return NULL;
    }

    return $template;
  }

}
