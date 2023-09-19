<?php

namespace AKlump\WebPackage\Traits;


trait ShellCommandTrait {

  /**
   * Wrapper for system() with exception throwing.
   *
   * @param string $command
   *
   * @return string
   *
   * @throws \RuntimeException If result code is not 0.
   */
  public function system(string $command): string {
    $result = system($command, $result_code);
    if (0 !== $result_code) {
      throw new \RuntimeException(sprintf('Failed: %s', $command));
    }

    return $result;
  }

  /**
   * Wrapper for exec() with exception throwing.
   *
   * @param string $command
   * @param array &$output
   *
   * @return string
   *
   * @throws \RuntimeException If result code is not 0.
   */
  public function exec(string $command, array &$output = []): string {
    $result = exec($command . ' 2>/dev/null', $output, $result_code);
    if (0 !== $result_code) {

      throw new \RuntimeException(sprintf('Failed: %s', $command));
    }

    return $result;
  }

}
