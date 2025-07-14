<?php

class BrevoMailService
{

	private $apiKey;

	public function __construct()
	{
		// Asume que la constante BREVO_API_KEY está definida en /core/config/config.php
		if (!defined('BREVO_API_KEY')) {
			die('Error: La clave de API de Brevo no está definida.');
		}
		$this->apiKey = BREVO_API_KEY;
	}

	/**
	 * Envía un correo electrónico utilizando la API de Brevo.
	 * @param string $destinatarioEmail
	 * @param string $destinatarioNombre
	 * @param string $asunto
	 * @param string $cuerpoHtml
	 * @return bool - true si el correo se envió correctamente, false si no.
	 */
	public function enviarEmail($destinatarioEmail, $destinatarioNombre, $asunto, $cuerpoHtml)
	{
		$url = 'https://api.brevo.com/v3/smtp/email';

		$datos = [
			'sender' => [
				'name' => NOMBRE_SITIO,
				// Reemplaza esto con un correo verificado en tu cuenta de Brevo
				'email' => 'hababel@gmail.com'
			],
			'to' => [
				[
					'email' => $destinatarioEmail,
					'name' => $destinatarioNombre
				]
			],
			'subject' => $asunto,
			'htmlContent' => $cuerpoHtml
		];

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($datos));
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'accept: application/json',
			'api-key: ' . $this->apiKey,
			'content-type: application/json'
		]);

		$response = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		// Brevo devuelve un código 201 (Created) cuando el envío es exitoso.
		return $http_code == 201;
	}
}
