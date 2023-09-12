<?php

namespace AKlump\WebPackage\Traits;


trait ImplodeTrait {

  public function or(array $items) {
    if (count($items) === 1) {
      return array_shift($items);
    }
    $last = array_pop($items);
    $line = implode(', ', $items);
    $line .= " or $last";

    return $line;
  }

  public function and(array $items) {
    if (count($items) === 1) {
      return array_shift($items);
    }
    $last = array_pop($items);
    $line = implode(', ', $items);
    $line .= " and $last";

    return $line;
  }

}
