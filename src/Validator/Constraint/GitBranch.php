<?php

namespace AKlump\WebPackage\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

class GitBranch extends Constraint {

  /**
   * To meet this constraint, $value must be one of these.
   *
   * @var array
   */
  public $options = [];

  public $messageNotInitialized = 'You have not initialized this repository yet.  "git init" and try again.';

  public $messageNoCommits = 'The (Git) repository is empty, make at least one commit and try again.';

  public $messageInvalidOption;

  /**
   * @param array $options
   *   This should be branch names or branch types depending upon context.
   * @param string $message_invalid_option
   *   A message if $value is not valid.
   * @param array|NULL $groups
   * @param $payload
   */
  public function __construct(
    array $options,
    string $message_invalid_option,
    array $groups = NULL,
    $payload = NULL
  ) {
    $this->options = $options;
    $this->messageInvalidOption = $message_invalid_option;
    parent::__construct([], $groups, $payload);
  }

}
