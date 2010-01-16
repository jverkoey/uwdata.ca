<?php

define('APPLICATION_PATH', dirname(__FILE__));
define('REL_PATH', '/');
include_once APPLICATION_PATH.REL_PATH.'paths.php';

include_once COMMON_PATH.'consts.php';

if( sizeof($argv) < 2 ) {
  echo 'Example usage:'."\n";
  echo 'php go.php calendar/scrape'."\n";
  exit;
}

if( !file_exists(CACHE_PATH) ) {
  mkdir(CACHE_PATH);
}

$path = $argv[1];
array_splice($argv, 1, 1);

if( strpos($path, '.php') === false ) {
  $path .= '.php';
}

require_once($path);
