<?php

namespace AKlump\WebPackage\Command;

use AKlump\WebPackage\Config\Config;
use AKlump\WebPackage\Config\ConfigManager;
use AKlump\WebPackage\Input\HumanInterface;
use AKlump\WebPackage\Traits\HasConfigTrait;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Filesystem\Path;

class ConfigCommand extends Command {

  use HasConfigTrait;

  protected static $defaultName = 'config';

  /**
   * @var \AKlump\WebPackage\Model\Context
   */
  protected $context;

  protected function configure() {
    $this
      ->setDescription("Display (or update) app configuration.")
      ->addOption('edit', 'e', InputOption::VALUE_NONE, 'Open the configuration file in $EDITOR.');
  }

  public function __construct(ContainerInterface $container) {
    $this->setConfig($container->get('config.loader')());
    $this->context = $container->get('context');
    parent::__construct();
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    if ($input->getOption('edit')) {
      $root_path = $this->context->getRootPath();
      $options = [HumanInterface::CHOICE_QUESTION_NONE];
      $config_files = [''];
      foreach ([
                 $root_path . '/.web_package/config',
                 $root_path . '/.web_package/local_config',
                 $root_path . '/.web_package/config.local',
               ] as $path) {
        $located = (new ConfigManager())->locateFile($path);
        if (!$located) {
          continue;
        }
        $option = Path::makeRelative($located, getcwd());
        $config_files[$option] = $located;
        $options[] = $option;
      }

      $helper = $this->getHelper('question');
      $question = new ChoiceQuestion('<question>Edit which file?</question> ', $options);
      $key = $helper->ask($input, $output, $question);
      $file = $config_files[$key] ?? NULL;
      if (!$file) {
        return Command::FAILURE;
      }
      system("\$EDITOR $file > `tty`");

      return Command::SUCCESS;
    }

    $this->output = $output;
    $config = $this->normalize($this->getConfig());

    $tables = [
      'Versioning' => [
        Config::VERSION_FILE,
        Config::CREATE_TAGS,
        Config::PATCH_PREFIX,
        Config::PRESERVE_PATCH_ZERO,
        Config::DO_VERSION_COMMIT,
      ],
      'Git Integration' => [
        'master',
        'develop',
        'remote',
        Config::PUSH_MASTER,
        Config::PUSH_DEVELOP,
        Config::PUSH_TAGS,
      ],
      'Invalid Configuration' => [
        'major_step',
        'minor_step',
        'patch_step',
      ],
    ];

    foreach ($tables as $heading => $table_keys) {
      $table = $this->getTable($heading);
      foreach ($table_keys as $table_key) {
        if (!isset($config[$table_key])) {
          continue;
        }
        $table->addRow([$table_key, $config[$table_key]]);
        unset($config[$table_key]);
      }
      $table->render();
    }

    // This will print everything else.
    if ($config) {
      $table = $this->getTable('');
      foreach ($config as $key => $value) {
        $table->addRow([$key, $value]);
      }
      $table->render();
    }

    return Command::SUCCESS;
  }

  private function getTable(string $heading) {
    $table = new Table($this->output);
    $table->setColumnWidth(0, 20);
    $table->setColumnWidth(1, 60);
    $table->setHeaderTitle($heading);

    return $table;
  }

  private function normalize($config) {
    $config['version_file'] = Path::makeRelative($config['version_file'], getcwd());
    if ($config['develop'] === $config['master']) {
      unset($config['develop']);
      unset($config[Config::PUSH_DEVELOP]);
    }

    $config = array_map(function ($value) {
      if (is_bool($value)) {
        return $value ? 'Y' : 'N';
      }

      return $value;
    }, $config);

    return $config;
  }
}
