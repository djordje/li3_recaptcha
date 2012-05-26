<?php

use lithium\data\Connections;
use lithium\core\Libraries;

/**
 * Do not change bootstrap connection
 * 
 * This is reCAPTCHA connection setup, you should add your reCAPTCHA keys
 * as Library config. This connection will merge keys to configuration.
 * 
 * Example:
 * {{{
 *		Libraries::add('li3_recaptcha', array(
 *			'keys' => array(
 *				'public' => 'your_public_recaptcha_key',
 *				'private' => 'your_private_recaptcha_key',
 *				'mailhide_public' => 'your_public_mailhide_recaptcha_key',
 *				'mailhide_private' => 'your_private_mailhide_recaptcha_key'
 *			)
 *		));
 * }}}
 */
Connections::add('recaptcha', array(
	'type' => 'lithium\net\http\Service',
	'timeout' => 10,
	'port' => 80,
	'host' => 'www.google.com',
	'version' => '1.0',
	'headers' => array(
		'Host' => "www.google.com",
		'Content-Type' => "application/x-www-form-urlencoded",
		'User-Agent' => "reCAPTCHA/PHP"
	),
	'keys' => Libraries::get('li3_recaptcha', 'keys') + array(
		'public' => null,
		'private' => null,
		'mailhide_public' => null,
		'mailhide_private' => null
	)
));

?>