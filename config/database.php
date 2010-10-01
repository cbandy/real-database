<?php

return array
(
	'default' => array
	(
		/** @see Database_MySQL::__construct() */
		'type' => 'MySQL',
		'charset' => 'utf8',
		'connection' => array
		(
			'hostname' => 'localhost',
			'port'     => NULL,
			'username' => NULL,
			'password' => NULL,
			'database' => 'kohana',
			'persistent' => FALSE,
			'flags' => NULL,
		),
	),
);
