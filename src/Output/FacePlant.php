<?php

namespace AKlump\WebPackage\Output;

use AKlump\LoftLib\Bash\Color;

/**
 * A very loud error message when the fan gets hit.
 *
 * @deprecated
 */
class FacePlant {

  public static function echo($message) {
    $tab = str_repeat(' ', 5);
    $length = mb_strlen($message . $tab . $tab);
    $border = str_repeat(' ', $length);
    echo Color::wrap("white on red", $border) . PHP_EOL;
    echo Color::wrap("white on red", $border) . PHP_EOL;
    echo Color::wrap("white on red", $tab . $message . $tab) . PHP_EOL;
    echo Color::wrap("white on red", $border) . PHP_EOL;
    echo Color::wrap("white on red", $border) . PHP_EOL;
  }
}
