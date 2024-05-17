<?php

namespace AKlump\WebPackage\Helpers;

/**
 * Throw an exception for a bash file, message and exit code.
 *
 * This class creates an exception where getFile returns a path with the correct
 * basename, and in some cases the correct line number where the exit code
 * occurred.
 */
class ThrowShellError {

  /**
   * @var string
   */
  private $scriptPath;

  /**
   * @param string $script_path
   *   Absolute path to the script that returned the error.
   */
  public function __construct(string $script_path) {
    $this->scriptPath = $script_path;
  }

  /**
   * @param string $exception_message
   * @param $exit_code
   * @param string $exception_class
   *   Optional, default to \RuntimeException
   *
   * @return void
   */
  public function __invoke(string $exception_message, $exit_code, string $exception_class = \RuntimeException::class) {
    $exception_message = (new GetAugmentedFailureMessage())($exception_message, $exit_code, $this->scriptPath);

    // Create a proxy file that will throw on the same line as our script, named
    // for our script so that the exception class contains the proper basename
    // and (hopefully) line number.
    $proxy_code = ['<?php'];
    $proxy_code += array_fill(1, $this->getLineNumber((int) $exit_code) - 1, "\n");
    $proxy_code[] = "throw new $exception_class('$exception_message', $exit_code);";
    $temp = sys_get_temp_dir();
    $ns = new \ReflectionClass($this);
    $ns = $ns->getShortName();
    $proxy_path = "$temp/$ns/" . basename($this->scriptPath);
    if (!file_exists(dirname($proxy_path))) {
      mkdir(dirname($proxy_path), 0700, TRUE);
    }
    file_put_contents($proxy_path, $proxy_code);

    // Now require the proxy file, which will throw the exception.
    try {
      require $proxy_path;
    }
    finally {
      unlink($proxy_path);
    }
  }

  /**
   * Try to sniff the line number where the script exited.
   *
   * @return int
   *   The best-guess line number where the script exited.
   */
  private function getLineNumber(int $exit_code): int {
    $number = 0;
    if (!file_exists($this->scriptPath)) {
      return $number;
    }
    $file = new \SplFileObject($this->scriptPath);
    while ($file->valid()) {
      $line = $file->fgets();
      $regex = '#exit\s+(\d+)#i';
      $line_number = $file->key() + 1;
      if (preg_match($regex, $line, $matches)) {
        if ((int) $matches[1] === $exit_code) {
          return $line_number;
        }
      }
    }

    // If we can find a line containing the exact result code, we'll return the
    // last line of the file and let the dev sort it out.
    return $line_number;
  }

}
