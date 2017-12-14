<?php

namespace App\ServiceProviders;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
	{
		app()->getContainer()[PHPMailer::class] = function ($c) {
			return function($configName = 'default', $configsOverride = []) {

				$defaultConfigs = app()->getConfig("settings.mail.{$configName}");
				$configs = array_merge($defaultConfigs, $configsOverride);

				$mail = new PHPMailer;
				$mail->CharSet = "UTF-8";
				$mail->isSMTP();
				$mail->isHTML(true);
				$mail->Host = $configs['host'];
				$mail->SMTPAuth = true;
				$mail->Username = $configs['username'];
				$mail->Password = $configs['password'];
				$mail->SMTPSecure = $configs['secure'];
				$mail->Port = $configs['port'];

				$mail->setFrom($configs['from'], $configs['fromName']);

				return $mail;
			};
		};

		return $next($request, $response);
	}

}