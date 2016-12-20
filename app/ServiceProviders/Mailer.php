<?php namespace App\ServiceProviders;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use PHPMailer;

class Mailer
{
	private $container;

	public function __construct($container)
	{
		$this->container = $container;
	}

	public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
	{
		$this->container['mailer'] = function ($c) {
			return function($to = [], $cc = [], $bcc = [], $subject, $body, $altBody, $attachments = [], $configName = 'default', $configsOverride = []) {

				$defaultConfigs = \Lib\App::instance()->getConfig("settings.mail.{$configName}");
				$configs = array_merge($defaultConfigs, $configsOverride);

				$mail = new PHPMailer;

				$mail->isSMTP();
				$mail->isHTML(true);
				$mail->Host = $configs['host'];
				$mail->SMTPAuth = true;
				$mail->Username = $configs['username'];
				$mail->Password = $configs['password'];
				$mail->SMTPSecure = $configs['secure'];
				$mail->Port = $configs['port'];

				$mail->setFrom($configs['from'], $configs['fromName']);

				foreach ((array)$to as $name => $email) {
					$mail->addAddress($email, is_string($name) ? $name : $email);
				}
				foreach ((array)$cc as $name => $email) {
					$mail->addCC($email, is_string($name) ? $name : $email);
				}
				foreach ((array)$bcc as $name => $email) {
					$mail->addBCC($email, is_string($name) ? $name : $email);
				}

				foreach ((array)$attachments as $attach) {
					$mail->addAttachment($attach);
				}

				$mail->Subject = $subject;
				$mail->Body    = $body;
				$mail->AltBody = $altBody;

				return $mail;
			};
		};

		return $next($request, $response);
	}

}