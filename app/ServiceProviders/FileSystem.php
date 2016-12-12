<?php namespace App\ServiceProviders;

use League\Flysystem\Filesystem as FlySystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Adapter\Ftp as FtpAdapter;


class FileSystem
{
	private $container;

	public function __construct($container) {
		$this->container = $container;
	}

	public function __invoke($request, $response, $next)
	{
		$this->container['filesystem'] = function ($c) {
			return function($configName = 'local', $configsOverride = []) {

				$defaultConfigs = \App\App::instance()->getConfig("settings.filesystem.{$configName}");
				$configs = array_merge($defaultConfigs, $configsOverride);

				$filesystem = null;
				switch ($configs['driver']) {
					case 'local':
						$adapter = new Local($configs['root']);
						$filesystem = new FlySystem($adapter);
						break;

					case 'ftp':
						$adapter = new FtpAdapter($configs);
						$filesystem = new FlySystem($adapter);
						break;

					default:
						throw new \Exception("filesystem driver not found");
						break;
				}
				return $filesystem;
			};
		};

		return $next($request, $response);
	}
}