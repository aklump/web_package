<?php

namespace AKlump\WebPackage;

class HookService {

  public function __construct(
    $name,
    $description,
    $version,
    $previous_version,
    $author,
    $homepage,
    $date_string
  ) {
    $this->data = [
      'name' => $name,
      'description' => $description,
      'version' => $version,
      'previous_version' => $previous_version,
      'author' => $author,
      'homepage' => $homepage,
      'date_string' => $date_string,
    ];
  }
}
