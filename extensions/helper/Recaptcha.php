<?php

/**
 * @copyright Copyright 2012, Djordje Kovacevic (http://djordjekovacevic.com)
 * @license   http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_recaptcha\extensions\helper;

use li3_recaptcha\security\Recaptcha as RecaptchaLib;
use lithium\core\Libraries;
use lithium\core\ConfigException;

class Recaptcha extends \lithium\template\Helper {
	
	/**
	 * reCAPTCHA error place holder
	 * Recaptcha::check() set it to 'incorrect-captcha-sol' on error
	 * @see \li3_recaptcha\security\Recaptcha
	 * @var string
	 */
	public static $error = null;
	
	/**
	 * reCAPTCHA templates for challenge field and mailhide url
	 * @var array
	 */
	protected $_strings = array(
		'challenge' => '
			<script type="text/javascript">
				var RecaptchaOptions = {theme: "white"}
			</script>
			<script type="text/javascript" src="{:src}/challenge?k={:publickey}{:errorpart}"></script>
			<noscript>
				<iframe src="{:src}/noscript?k=k{:publickey}{:errorpart}" height="300" width="500" frameborder="0"></iframe><br />
				<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
				<input type="hidden" name="recaptcha_response_field" value="manuel_challenge" />
			</noscript>
		',
		'mailhide' => '
			{:emailpart_0}
			<a href="{:url}"
				onclick="window.open("{:url}", "", "toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=500,height=300")"
				title="Reveal this e-mail address" >...</a>
			@{:emailpart_1}
		'
	);
	
	/**
	 * Create reCAPTCHA challenge field
	 * @return string Rendered reCAPTCHA challenge field template
	 */
	public function challenge() {
		$config = Libraries::get('li3_recaptcha', 'keys');
		$src = $this->_context->request()->scheme .  '://www.google.com/recaptcha/api';
		$errorpart = '';
		if (static::$error) {
			$errorpart = '&amp;error=' . static::$error;
		}
		return $this->_render(__METHOD__, 'challenge', array(
			'src' => $src,
			'publickey' => $config['public'],
			'errorpart' => $errorpart
		));
	}
	
	/**
	 * Create reCAPTCHA mailhide url
	 * @param string $email Email to hide
	 * @return string Rendered reCAPTCHA mailhide url template
	 */
	public function mailhide($email) {
		$emailParts = RecaptchaLib::mailhideEmailParts($email);
		$url = RecaptchaLib::mailhideUrl($email);
		return $this->_render(__METHOD__, 'mailhide', array(
			'url' => $url,
			'emailpart_0' => $emailParts[0],
			'emailpart_1' => $emailParts[1]
		));
	}
	
}

?>