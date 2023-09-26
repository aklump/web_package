<?php

namespace AKlump\WebPackage\Command;

use AKlump\WebPackage\Access\IsInitialized;
use AKlump\WebPackage\Config\Config;
use AKlump\WebPackage\Config\ConfigDefaults;
use AKlump\WebPackage\Config\ConfigManager;
use AKlump\WebPackage\Helpers\GetAllBranches;
use AKlump\WebPackage\Helpers\GetAllTemplates;
use AKlump\WebPackage\Input\HumanInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
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

  /**
   * @var \AKlump\WebPackage\Model\Context
   */
  private $context;

  protected function configure() {
    $this->setDescription('Initialize the current directory.')
      ->setHelp('This command allows you to see all available versions to be swapped');
  }

  public function __construct(ContainerInterface $container, string $installation_dir, string $template_dir, $filesystem) {
    parent::__construct();
    if (!Path::isAbsolute($installation_dir)) {
      $installation_dir = getcwd() . "/$installation_dir";
    }
    $this->container = $container;
    $this->context = $container->get('context');
    $this->dir = $installation_dir;
    $this->templateDir = $template_dir;
    $this->filesystem = $filesystem;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $this->input = $input;
    $this->output = $output;

    try {
      if ((new IsInitialized())->access()) {
        $root = $this->context->getRootPath();
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

      // TODO Need to DI CM.
      $this->savePath = (new ConfigManager())
        ->locateFile($this->dir . '/config');
      $extension = strtolower(Path::getExtension($this->savePath));
      if (!in_array($extension, ['yml', 'yaml'])) {
        throw new \InvalidArgumentException('Unsupported config file type: %s.', $extension);
      }

      $config = $this->container->get('config.loader')($template);

      // This next step will sniff the environment if git is already used and/or
      // ask the user for input.
      $config[Config::MAIN_BRANCH] = $this->getMainBranch($config[Config::MAIN_BRANCH]);
      $config[Config::DEVELOP_BRANCH] = $this->getDevelopBranch($config[Config::DEVELOP_BRANCH]) ?? $config[Config::MAIN_BRANCH];

      // Prepare contents and save configuration.
      $contents = $this->prepareValuesForWrite($config);
      $contents = array_filter($contents, function ($key) {
        // Some keys need not be saved.
        return !in_array($key, array_merge([
          Config::INITIAL_VERSION,
        ], UpgradeCommand::REMOVED_KEYS));
      }, ARRAY_FILTER_USE_KEY);
      $contents = Yaml::dump($contents, 6);
      if (!file_put_contents($this->savePath, $contents)) {
        throw new \RuntimeException(sprintf('Could not save to: %s', $this->savePath));
      }

      $this->handleInitVersion($config[Config::INITIAL_VERSION] ?? ConfigDefaults::INITIAL_VERSION);
      $output->writeln('<info>Created ./.web_package directory</info>');
    }
    catch (\Exception $exception) {
      $output->writeln(sprintf("<error>%s</error>", $exception->getMessage()));

      return Command::FAILURE;
    }

    return Command::SUCCESS;
  }


  private function handleInitVersion(string $init_version): void {
    if (!$init_version) {
      return;
    }
    $scribe = $this->container->get('scribe.factory')();
    if (!$scribe->read()) {
      $scribe->write($init_version);
    }
  }

  private function prepareValuesForWrite(array $config): array {
    if (Path::isAbsolute($config[Config::VERSION_FILE])) {
      $config[Config::VERSION_FILE] = Path::makeRelative($config[Config::VERSION_FILE], $this->context->getRootPath());
    }

    if ($config[Config::DEVELOP_BRANCH] === $config[Config::MAIN_BRANCH]) {
      unset($config[Config::DEVELOP_BRANCH]);
      unset($config[Config::PUSH_DEVELOP]);
    }

    ksort($config);

    return $config;
  }

  private function tryGetTemplatePath(string $template): string {
    if (GetAllTemplates::DEFAULT === $template) {
      $path_to_template = $this->context->getServerHome() . '/.web_package/config';
    }
    else {
      $path_to_template = $this->context->getServerHome() . '/.web_package/config_' . $template;
    }
    if (!$this->filesystem->exists($path_to_template)) {
      throw new \InvalidArgumentException(sprintf('Template "%s" not found at %s ', $template, $path_to_template));
    }

    return $path_to_template;
  }

  private function getMainBranch(string $fallback): string {
    $branches = (new GetAllBranches())();
    $existing = array_values(array_filter($branches, function ($name) use ($fallback) {
      return in_array($name, [
        'main',
        ConfigDefaults::MAIN_BRANCH,
        \AKlump\WebPackage\Model\GitFlow::MASTER,
        $fallback,
      ]);
    }))[0] ?? NULL;
    if ($existing) {
      return $existing;
    }

    $helper = $this->getHelper('question');
    $question = new Question('<question>Main branch name?</question> ');
    $question->setAutocompleterValues([
      'main',
      'master',
      \AKlump\WebPackage\Model\GitFlow::MASTER,
      ConfigDefaults::MAIN_BRANCH,
      $fallback,
    ]);
    $name = $helper->ask($this->input, $this->output, $question);

    return $name ?: $fallback;
  }

  private function getDevelopBranch(string $fallback): ?string {
    $branches = (new GetAllBranches())();
    $existing = array_values(array_filter($branches, function ($name) use ($fallback) {
      if (in_array($name, [
        \AKlump\WebPackage\Model\GitFlow::DEVELOP,
        ConfigDefaults::DEVELOP_BRANCH,
        $fallback,
      ])) {
        return TRUE;
      }

      return strstr($name, 'dev');
    }))[0] ?? NULL;
    if ($existing) {
      return $existing;
    }

    $helper = $this->getHelper('question');
    $question = new Question('<question>Will you use a "develop" branch?</question> ', 'no');
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

    $question = new Question('<question>Branch name?</question> ');
    $question->setAutocompleterValues([
      \AKlump\WebPackage\Model\GitFlow::DEVELOP,
      ConfigDefaults::DEVELOP_BRANCH,
      $fallback,
    ]);
    $name = $helper->ask($this->input, $this->output, $question);

    return $name ?: NULL;
  }

  public function getTemplate(): ?string {
    $choices = array_keys((new GetAllTemplates())());

    array_unshift($choices, HumanInterface::CHOICE_QUESTION_NONE);
    $helper = $this->getHelper('question');
    $question = new ChoiceQuestion("<question>Use a template?</question> ", $choices);
    $template = $helper->ask($this->input, $this->output, $question);
    if ($template === HumanInterface::CHOICE_QUESTION_NONE) {
      return NULL;
    }

    return $template;
  }

}
