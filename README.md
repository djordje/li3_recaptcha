# reCAPTCHA plugin for Lithium PHP framework

Plugin that enable easy interactions with [Googles reCAPTCHA API](http://www.google.com/recaptcha)

**Created by** [Djordje Kovacevic](http://github.com/djordje) 2012-05-26

**Thanks to** @gwoo and @nateabele for helping on #li3 chanel!

## Installationand configuration

Checkout the code to either of your library directories:

	cd libraries
	git clone git://github.com/djordje/li3_recaptcha.git

Include library in your `app/config/bootstrap/libraries.php`,
and pass your reCAPTCHA keys as config:

	Libraries::add('li3_recaptcha', array(
		'keys' => array(
			'public' => 'your_public_recaptcha_key',
			'private' => 'your_private_recaptcha_key',
			'mailhide_public' => 'your_public_mailhide_recaptcha_key',
			'mailhide_private' => 'your_private_mailhide_recaptcha_key'
		)
	));

## Usage

This plugin provide template helper `li3_recaptcha\extensions\helper\Recaptcha`,
you can use it in your template `$this->recaptcha->{method}()`

If you want to create reCAPTCHA challenge field you add `$this->recptcha->challenge()` to your form

For hidding email you use `$this->recaptcha->mailhide($email)`

To check your reCAPTCHA challenge answare you add to action in your controller something like this:

	if ($this->request->data && Recaptcha::check($this->request)) {
		// Do some stuff for users that passed reCAPTCHA check
	}

## Testing

There is not unit tests for this plugin because reCAPTCHA does not provide testing API