<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package  Database
 *
 * Database connection settings, defined as arrays, or "groups". If no group
 * name is used when loading the database library, the group named "default"
 * will be used.
 *
 * Each group can be connected to independently, and multiple groups can be
 * connected at once.
 *
 * Group Options:
 *  benchmark     - Enable or disable database benchmarking
 *  persistent    - Enable or disable a persistent connection
 *  connection    - Array of connection specific parameters; alternatively,
 *                  you can use a DSN though it is not as fast and certain
 *                  characters could create problems (like an '@' character
 *                  in a password):
 *                  'connection'    => 'mysql://dbuser:secret@localhost/kohana'
 *  character_set - Database character set
 *  table_prefix  - Database table prefix
 *  object        - Enable or disable object results
 *  cache         - Enable or disable query caching
 *	escape        - Enable automatic query builder escaping
 */
$config['default'] = array
(
	'benchmark'     => TRUE,
	'persistent'    => FALSE,
	'connection'    => array(
		'type'     => 'mysql',
		'user'     => 'uwdataapi',
		'pass'     => 'UwD4tA4p1123!',
		'host'     => 'localhost',
		'port'     => FALSE,
		'socket'   => FALSE
		//'database' => 'uwdata20092010'
	),
	'character_set' => 'utf8',
	'table_prefix'  => '',
	'object'        => TRUE,
	'cache'         => TRUE,
	'escape'        => TRUE
);

$tables = array(
  '20092010',
  '20082009',
  '20072008',
  '20062007',
  '20052006',
  '20042005',
  '20032004',
  '20022003',
  '20012002',
  //'20002001',
);
foreach ($tables as $years) {
  $config['uwdata'.$years] = $config['default'];
  $config['uwdata'.$years]['connection']['database'] = 'uwdata'.$years;
}

$config['uwdata_schedule'] = $config['default'];
$config['uwdata_schedule']['connection']['database'] = 'uwdata_schedule';

