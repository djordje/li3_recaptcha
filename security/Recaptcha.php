<?php

/**
 * @copyright Copyright 2012, Djordje Kovacevic (http://djordjekovacevic.com)
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_recaptcha\security;

use lithium\core\Libraries;
use lithium\data\Connections;
use lithium\core\ConfigException;
use li3_recaptcha\extensions\helper\Recaptcha as RecaptchaHelper;
use BadFunctionCallException;
use RuntimeException;

/**
 * The `Recaptcha` class provide common interface to work with reCAPTCHA 
 */

class Recaptcha {
	
	/**
	 * Check request reCAPTCHA validity
	 * This method return `true` or `false` after validation, and set error in
	 * helper. If `true` error is set to null, otherwise `'incorrect-captcha-sol'`
	 * 
	 * Example:
	 * {{{
	 *		class YourController extends \lithium\action\Controller {
	 *			public function index() {
	 *				if ($this->request->data) {
	 *					if (!Recaptcha::check($this->request)) {
	 *						return;
	 *					}
	 *				}
	 *			}
	 *		}
	 * }}}
	 * @param object $request Pass request object to check method
	 * @return boolean
	 * @throws ConfigException
	 * @throws RuntimeException 
	 */
	public static function check(\lithium\net\http\Request $request) {
		$config = Libraries::get('li3_recaptcha', 'keys');
		if (!$config['private']) {
			throw new ConfigException(
				'To use reCAPTCHA you must get API key from'.
				'https://www.google.com/recaptcha/admin/create'
			);
		}
		if (!$request->env('SERVER_ADDR')) {
			throw new RuntimeException(
				'For security reasons, you must pass the remote ip to reCAPTCHA'
			);
		}
		$data = array(
			'privatekey' => $config['private'],
			'remoteip'   => $request->env('SERVER_ADDR'),
			'challenge'  => null,
			'response'   => null
		);
		if ($request->data) {
			$data['challenge'] = $request->data['recaptcha_challenge_field'];
			$data['response']  = $request->data['recaptcha_response_field'];
		}
		if (!$data['challenge'] || !$data['response']) {
			RecaptchaHelper::$error = 'incorrect-captcha-sol';
			return false;
		}
		$service = Connections::get('recaptcha');
		$serviceRespond = explode("\n", $service->post('/recaptcha/api/verify', $data));
		if ($serviceRespond[0] == 'true') {
			RecaptchaHelper::$error = null;
			return true;
		} else {
			RecaptchaHelper::$error = 'incorrect-captcha-sol';
			return false;
		}
	}

	/**
	 * Create URL for hiding email
	 * @param string $email
	 * @return string
	 * @throws ConfigException 
	 */
	public static function mailhideUrl($email) {
		$config = Libraries::get('li3_recaptcha', 'keys');
		if (!$config['mailhide_public'] || !$config['mailhide_private']) {
			throw new ConfigException(
				'To use reCAPTCHA you must get API key from '.
				'https://www.google.com/recaptcha/admin/create'
			);
		}
		$key = pack('H*', $config['mailhide_privatekey']);
		$cryptMail = static::_aesEncrypt($email, $key);
		$url  = 'http://www.google.com/recaptcha/mailhide/d?k=';
		$url .= $config['mailhide_public'];
		$url .= '&c=';
		$url .= static::_mailhideUrlBase64($cryptMail);
		return $url;
	}
	
	/**
	 * Create email with hidden parts
	 * 
	 * Example:
	 *  {{{
	 *		self::mailhidePartsEmail(example@example.com);
	 *		//return array('exam', 'example.com');
	 *  }}}
	 * @param string $email
	 * @return array First key is shorten part before `@`, second is domain part
	 */
	public static function mailhideEmailParts($email) {
		$parts = explode('@', $email);
		switch (strlen($parts[0])) {
			case 0:
			case 1:
			case 2:
			case 3:
			case 4:
				$parts[0] = substr($parts[0], 0, 1);
				break;
			case 5:
			case 6:
				$parts[0] = substr($parts[0], 0, 3);
				break;
			default:
				$parts[0] = substr($parts[0], 0, 4);
				break;
		}
		return $parts;
	}
	
	/**
	 * Gets a URL where the user can sign up for reCAPTCHA. If your application
	 * has a configuration page where you enter a key, you should provide a link
	 * using this method.
	 * @param string $domain The domain where the page is hosted
	 * @param string $appname The name of your application
	 * @return string
	 */
	public static function getSignupUrp($domain = null, $appname = null) {
		$qs = static::_qsEncode(array('domains' => $domain, 'app' => $appname));
		return 'https://www.google.com/recaptcha/admin/create?' . $qs;
	}

	/**
	 * Create query string from _array_ of `'key' => 'value'` pairs
	 * 
	 * Example:
	 * {{{
	 *		$qs = static::_qsEncode(array(
	 *			'key' => 'value',
	 *			'key2' => 'value2'
	 *		));
	 *		//return `key=value&key2=value2`
	 * }}}
	 * 
	 * @param array $data `'key' => 'value'` paris for converting to query string
	 * @return string
	 */
	protected static function _qsEncode($data) {
		$qs = array();
		foreach ($data as $key => $val) {
			$qs[] = $key . '=' . urlencode(stripslashes($val));
		}
		return join('&', $qs);
	}
	
	/**
	 * Aes padding of input string
	 * @param string $input
	 * @return string Aes padded input string
	 */
	protected static function _aesPad($input) {
		$blockSize = 16;
		$numpad = $blockSize - (strlen($input) % $blockSize);
		return str_pad($input, srtlen($input) + $numpad, chr($numpad));
	}
	
	/**
	 * Aes encrypt
	 * @param string $key The key with which the data will be encrypted
	 * @param string $data The data that will be encrypted with the given _cipher_
	 * @return string Encrypted data string
	 * @throws BadFunctionCallException 
	 */
	protected static function _aesEncrypt($data, $key) {
		if (!function_exists('mcrypt_encrypt')) {
			throw new BadFunctionCallException(
				'To use reCAPTCHA Mailhide, you need to have the mcrypt php module installed.'
			);
		}
		return mcrypt_encrypt(
			MCRYPT_RIJNDAEL_128,
			$key,
			$data,
			MCRYPT_MODE_CBC,
			"\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0"
		);
	}
	
	/**
	 * Encode base64 input string, mailhide url, and replace not allowed chars
	 * @param string $input 
	 * @return string 
	 */
	protected static function _mailhideUrlBase64($input) {
		return strtr(base64_encode($input), '+/', '-_');
	}
}

?>