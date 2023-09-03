<?php
// Writes a file called built.txt to a build directory `built/test/built.txt`
// with the contents "Hello World"
$path = $argv[7] . '/dist/php/test/built.txt';
@mkdir(dirname($path), 0777, TRUE);
file_put_contents($path, "Hello World" . PHP_EOL);

if (file_exists($path)) {
  echo "It worked! To see for yourself type: cat php/test/built.txt" . PHP_EOL;
  echo "You should see the phrase: Hello World" . PHP_EOL;
}
