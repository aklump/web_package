<?php

namespace AKlump\WebPackage\Hooks;

use AKlump\LoftLib\Storage\FilePath;
use AKlump\WebPackage\Helpers\GetAugmentedFailureMessage;
use AKlump\WebPackage\Helpers\GetHooksDirectory;
use AKlump\WebPackage\Helpers\GetRootPath;
use AKlump\WebPackage\Helpers\ThrowShellError;
use AKlump\WebPackage\HookException;
use AKlump\WebPackage\HookService;
use AKlump\WebPackage\Output\Icons;
use Exception;
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

  /**
   * @var array
   *   An array that is shared with all PHP hooks for value sharing.
   */
  private $sandbox;

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
    $this->sandbox = [];
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
        $this->output->writeln(sprintf('<info>Executing hook: %s ...</info>', $id));
        $exit_code = $callable();
        if (255 === $exit_code) {
          $this->output->writeln(sprintf('<info>%s... Hook skipped (code 255)</info>', Icons::SKIP, $id));
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
        Glob::glob("$hooks_dir{$filter}.sh"),
        Glob::glob("$hooks_dir{$filter}.php"),
      );
    }

    usort($hooks, function ($a, $b) {
      return strcasecmp(basename($a), basename($b));
    });

    return $hooks;
  }

  /**
   * Execute a PHP hook.
   *
   * @return int
   *   0 or 255, 255 means the hook was skipped but keep buildling.
   * @throws \Exception If the build should stop.
   */
  private function executePhp() {
    require_once WEB_PACKAGE_ROOT . '/includes/wp_functions.php';
    $args = $this->getHookArgs();

    $build = new HookService(
      FilePath::create(__DIR__ . '/..'),
      FilePath::create($args[9]),
      FilePath::create($args[7]),
      $args[3],
      $args[4],
      $args[2],
      $args[1],
      $args[6],
      $args[5],
      $args[8]
    );

    // Include a bootstrap file defined in the project using WP.
    $local_include = $args[13] . '/bootstrap.php';
    if (file_exists($local_include)) {
      require_once $local_include;
    }

    $stash_wd = getcwd();
    $root = (new GetRootPath())(getcwd());
    $hook_path = $this->event->getHook();
    if (!chdir($root)) {
      throw new \RuntimeException(sprintf('Failed to chdir; cannot execute hook: %s', $hook_path));
    }

    try {
      set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
      });

      // This will trap any calls to `exit()` which is incorrect for PHP hooks.
      $hook_exit = (object) [];
      register_shutdown_function(function ($hook_exit) {
        if (!isset($hook_exit->status)) {
          $this->output->writeln('<error>Failing PHP hooks must only throw exceptions; do not call `exit()`</error>');
        }
      }, $hook_exit);

      // Legacy support of $argv.
      $argv = [0 => $hook_path] + $args;
      // $build has to be there for legacy reasons as well.
      $run_in_sandbox = function () use ($argv, $build) {
        $sandbox =& $this->sandbox;
        // Isolate this hook from any other.
        require $argv[0];
      };
      $run_in_sandbox();
      $hook_exit->status = 0;
    }
    catch (Exception $exception) {
      $hook_exit->status = (int) $exception->getCode();
      if (!$hook_exit->status) {
        $hook_exit->status = 1;
      }
      if ($hook_exit->status === 255) {
        $this->output->writeln($exception->getMessage());
      }
      else {
        $augmented_message = (new GetAugmentedFailureMessage())($exception->getMessage(), $exception->getCode(), $hook_path);
        $reflectionObject = new \ReflectionObject($exception);
        $message_prop = $reflectionObject->getProperty('message');
        $message_prop->setAccessible(TRUE);
        $message_prop->setValue($exception, $augmented_message);
        throw $exception;
      }
    }
    finally {
      restore_error_handler();
      chdir($stash_wd);
    }

    return $hook_exit->status;
  }

  /**
   * Execute a Shell hook.
   *
   * @return int
   *   0 or 255, 255 means the hook was skipped but keep buildling.
   * @throws \Exception If the build should stop.
   */
  private function executeShell(): int {

    $quote_value = function ($value): string {
      $value = trim($value, "'");
      if (strstr($value, "'") !== FALSE) {
        return "\"$value\"";
      }

      return "'$value'";
    };

    $shell_code = '';
    foreach ($this->getHookConstants() as $key => $value) {
      if (empty($value)) {
        continue;
      }
      $value = $quote_value($value);
      $shell_code .= ";export $key=$value";
    }

    $shell_code .= ';' . WEB_PACKAGE_ROOT . '/includes/hook_runner.sh';

    $args = $this->getHookArgs();
    foreach ($args as $arg) {
      $arg = $quote_value($arg);
      $shell_code .= " $arg";
    }
    $shell_code = trim($shell_code, ';');
    $message = system($shell_code, $result_code);

    // Throw an exception for build-stopping situations.
    if ($result_code > 0 && $result_code < 255) {
      $script_path = $this->event->getHook();
      (new ThrowShellError($script_path))($message, $result_code, \InvalidArgumentException::class);
    }

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
