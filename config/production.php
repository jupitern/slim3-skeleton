<?php
return [
	'settings' => [
		// debug options
		'debug' => true,
		// url config. Url must end with a slash '/'
		'baseUrl' => 'http://localhost:8080/',
		'indexFile' => true,
		// database configs
		'database' => [
			// default db connection settings
			'default' => [
				'driver'    => 'mysql',
				'host'      => '',
				'database'  => '',
				'username'  => '',
				'password'  => '',
				'charset'   => 'utf8',
				'collation' => 'utf8_unicode_ci',
				'prefix'    => ''
			],
			// sqlsrv config
//			'default' => array(
//				'driver' 	=> 'sqlsrv',
//				'host' 		=> '',
//				'database' 	=> '',
//				'username' 	=> '',
//				'password' 	=> '',
//				'prefix' 	=> '',
//			),
		],
	]
];