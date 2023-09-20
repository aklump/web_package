<?php

namespace AKlump\WebPackage\Traits;


use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validation;

/**
 * Add to classes that want to validate using constraints.
 */
trait ValidationTrait {

  /**
   * @var \Symfony\Component\Console\Output\OutputInterface
   */
  protected $output;

  /**
   * Check a value against one or more constraints.
   *
   * @param $value
   * @param array $constraints
   *
   * @return void
   *
   * @see self::handleViolations()
   */
  protected function validate($value, array $constraints): void {
    $validator = Validation::createValidator();
    $violations = $validator->validate($value, $constraints);
    if (empty($this->violations)) {
      $this->violations = $violations;
    }
    else {
      $this->violations->addAll($violations);
    }
  }

  /**
   * Output any violation messages to the user.
   *
   * @return int
   *   The total violations handled.
   */
  protected function handleViolations(): int {
    $total_violations = count($this->violations);
    if (0 !== $total_violations) {
      foreach ($this->violations as $violation) {
        if (!$this->output instanceof OutputInterface) {
          throw new \RuntimeException('$this->output has not been set.');
        }
        $this->output->writeln('<error>' . $violation->getMessage() . '</error>');
      }
    }

    return $total_violations;
  }

}
