<?php

namespace App\ServiceProviders;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use League\Flysystem\Filesystem as FlySystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Adapter\Ftp as FtpAdapter;
use League\Flysystem\FilesystemInterface;


class FileSystem
{

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
	{
		app()->getContainer()[FilesystemInterface::class] = function ($c) {
			return function($configName = 'local', $configsOverride = []) {

				$defaultConfigs = app()->getConfig("settings.filesystem.{$configName}");
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