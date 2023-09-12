<?php

namespace AKlump\WebPackage\Hooks;

use AKlump\WebPackage\Helpers\GetHooksDirectory;
use AKlump\WebPackage\HookException;
use Jawira\CaseConverter\Convert;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Path;
use Webmozart\Glob\Glob;

class HookManager {

  const EXTENSIONS = ['sh', 'php'];

  /**
   * @var \AKlump\WebPackage\Hooks\HookEvent
   */
  private $event;

  /**
   * @var \Symfony\Component\Console\Output\OutputInterface
   */
  private $output;

  public function __construct(OutputInterface $output, HookEvent $event) {
    $this->output = $output;
    $this->event = $event;
  }

  /**
   * @param string $type
   *   One of: build, unbuild, dev.
   * @param string $filter
   *   Optional. See \AKlump\WebPackage\Hooks\HookManager::findHooksByType().
   *
   * @throws \AKlump\WebPackage\HookException
   */
  public function run(string $type, string $filter = '*'): void {
    $files = $this->findHooksByType($type, $filter);
    if (empty($files)) {
      if ($filter) {
        $this->output->writeln(sprintf('<error>There are no %s-type hooks to run matching "%s".</error>', $type, $filter));
      }
      else {
        $hooks_dir = (new GetHooksDirectory())($type);
        $hooks_dir = Path::makeRelative($hooks_dir, getcwd()) . '/';
        $this->output->writeln(sprintf('<error>%s is empty; nothing to run.</error>', $hooks_dir));
      }

      return;
    }
    foreach ($files as $path) {
      $callable = NULL;
      $extension = strtolower(Path::getExtension($path));
      if ('php' === $extension) {
        $callable = [$this, 'executePhp'];
      }
      if ('sh' === $extension) {
        $callable = [$this, 'executeShell'];
      }
      if ($callable) {
        $this->event->setHook($path);
        $id = Path::makeRelative($path, dirname($path, 2));
        $this->output->writeln(sprintf('<info>Executing hook: %s...</info>', $id));
        $exit_code = $callable();
        if (0 != $exit_code) {
          throw new HookException(sprintf("%s exited with code %d", $id, $exit_code));
        }
      }
    }
  }

  /**
   * Find hook filepaths.
   *
   * @param string $type
   * @param string $filter
   *   An optional substring or glob to match filters against.
   *
   * @return string[]
   *   An array of hook paths for a given $type after applying filter.
   */
  public function findHooksByType(string $type, string $filter = ''): array {
    $has_extension = FALSE;
    if ($filter) {
      $regex = '\.(' . implode('|', self::EXTENSIONS) . ')$';
      $has_extension = (bool) preg_match("/$regex/i", $filter);
    }

    // This will mimic the way legacy worked, which was to match substrings
    // without the use of glob chars.
    if ($filter && !strstr($filter, '*')) {
      if ($has_extension) {
        $filter = "*$filter";
      }
      else {
        $filter = "*$filter*";
      }
    }
    elseif (empty($filter)) {
      $filter = '*';
    }

    $hooks_dir = (new GetHooksDirectory())($type);
    if ($has_extension) {
      $hooks = Glob::glob("$hooks_dir/$filter");
    }
    else {
      $hooks = array_merge(
        Glob::glob("$hooks_dir/$filter.sh"),
        Glob::glob("$hooks_dir/$filter.php"),
      );
    }

    usort($hooks, function ($a, $b) {
      return strcasecmp(basename($a), basename($b));
    });

    return $hooks;
  }

  private function executeShell(): int {
    $shell_code = '';

    foreach ($this->getHookConstants() as $key => $value) {
      if (empty($value)) {
        continue;
      }
      $shell_code .= ";export $key='" . trim($value, "'") . "'";
    }

    $shell_code .= ';' . WEB_PACKAGE_ROOT . '/includes/hook_runner.sh';

    $args = $this->getHookArgs();
    foreach ($args as $arg) {
      $shell_code .= " '$arg'";
    }
    $shell_code = trim($shell_code, ';');
    system($shell_code, $result_code);

    return $result_code;
  }

  private function getHookConstants(): array {
    $ref = new ReflectionClass($this->event);
    $constants = [];
    foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
      $method = $method->getName();
      if (!preg_match('/^get(.+)/', $method, $matches)) {
        continue;
      }
      $value = $this->event->$method();
      if ($value) {
        $key = strtoupper((new Convert($matches[1]))->toSnake());
        $constants[$key] = $value;
      }
    }

    return $constants;
  }

  private function getHookArgs(): array {
    return [
      1 => $this->event->getPreviousVersion(),
      2 => $this->event->getVersion(),
      3 => $this->event->getPackageName(),
      4 => $this->event->getDescription(),
      5 => $this->event->getHomepage(),
      6 => $this->event->getAuthor(),
      7 => $this->event->getRoot(),
      8 => $this->event->getDateTime(),
      9 => $this->event->getInfoFile(),
      10 => dirname($this->event->getHook()),
      11 => $this->event->getRoot() . '/.web_package',
      12 => $this->event->getWebPackageRoot(),
      13 => dirname((new GetHooksDirectory())('*')),
    ];
  }

}
