<?php

/**
 * @file Set the version in the Symfony Console controller.
 */
$controller = "htaccess_manager.php";
$content = file_get_contents($controller);
$content = preg_replace('#setVersion\((.+?)\)#', 'setVersion(\'' . $argv[2] . '\')', $content);
file_put_contents($controller, $content);
