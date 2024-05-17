<?php

/** @var \Symfony\Component\EventDispatcher\EventDispatcher $dispatcher */


use AKlump\Knowledge\Events\GetVariables;
use AKlump\LoftLib\Code\Markdown;
use AKlump\LoftDocs\DynamicContent\PhpClassMethodReader;

$dispatcher->addListener(GetVariables::NAME, function (GetVariables $event) {
  $app_root = realpath($event->getPathToSource() . '/../');

  // Load the class(es) to be scanned.
  require_once "$app_root/src/HookService.php";

  // Define the classes to read.
  $reader = new PhpClassMethodReader();
  $reader->addClassToScan('\AKlump\WebPackage\HookService', [

    // But we want to exclude the method called 'getBrowser', so we use the
    // second parameter which defines a filter.
    PhpClassMethodReader::EXCLUDE,
    ['/^__/'],
  ]);

  // Convert the scanned data into a markup table for each group, in this
  // example there is only one group, because we are using only one class.
  foreach ($reader->scan() as $group => $methods) {
    $methods = array_map(function ($method) use ($group) {
      return [$group => '<strong>' . $method['name'] . '</strong> <em>(' . implode(', ', $method['params']) . ')</em>'];
    }, $methods);
    $markdown = Markdown::table($methods) . PHP_EOL;
    $event->setVariable($group, $markdown);
  }

});
