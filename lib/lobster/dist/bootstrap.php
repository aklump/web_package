<?php
/**
 * @file
 * Boostrap the PHP portion of Lobster
 */
require_once getenv('LOBSTER_ROOT') . '/functions.php';
if (file_exists($file = getenv('LOBSTER_APP_ROOT') . '/includes/bootstrap.php')) {
  require_once $file;
}
if (file_exists($file = getenv('LOBSTER_APP_ROOT') . '/includes/functions.php')) {
  require_once $file;
}
