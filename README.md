[![project status](http://stillmaintained.com/djordje/li3_recaptcha.png)]
(http://stillmaintained.com/djordje/li3_recaptcha)

# reCAPTCHA plugin for [Lithium PHP framework](http://github.com/UnionOfRAD/lithium)

Plugin that enable easy interactions with [Googles reCAPTCHA API](http://www.google.com/recaptcha)

**Created by** [Djordje Kovacevic](http://github.com/djordje) 2012-05-26

**Thanks to** @[gwoo](http://github.com/gwoo) and
@[nateabele](http://github.com/nateabele) for helping on #li3 chanel!

## Installation and configuration

**1a.** Checkout the code to either of your library directories:

	cd libraries
	git clone git://github.com/djordje/li3_recaptcha.git

**1b.** Or if you use **composer** you can add this to your `composer.json` file:

```json

	{
		"require": {
			"djordje/li3_recaptcha": "v1.0.0"
		}
	}

```

**2.** Include library in your `app/config/bootstrap/libraries.php`,
and pass your reCAPTCHA keys as config:

```php

	Libraries::add('li3_recaptcha', array(
		'keys' => array(
			'public' => 'your_public_recaptcha_key',
			'private' => 'your_private_recaptcha_key',
			'mailhide_public' => 'your_public_mailhide_recaptcha_key',
			'mailhide_private' => 'your_private_mailhide_recaptcha_key'
		)
	));

```

**WARNING:** You must provide `public` and `private` keys or this plugin will not work!

Additionaly you can pass `options` for configuring `RecaptchaOptions` look and feel,
this will be global for your application, but you can override it by passing new `options`
to helpers `challenge()` method.

**See [Customizing the Look and Feel of reCAPTCHA](https://developers.google.com/recaptcha/docs/customization)
for available options.**

```php

	Libraries::add('li3_recaptcha', array(
		'options' => array(
			'theme' => 'white'
		)
	));

```

## Usage

This plugin provide template helper `li3_recaptcha\extensions\helper\Recaptcha`,
you can use it in your template `$this->recaptcha->{method}()`.

**Challenge**

If you want to create reCAPTCHA challenge field you add `$this->recptcha->challenge()` to your form.

You can also pass `RecaptchaOptions` as param to `challenge()` method:

```php

	$this->recaptcha->challenge(array(
		'theme' => 'blackglass'
	));

```

**Mailhide**

For hidding email you use `$this->recaptcha->mailhide($email)`.

**Checking challenge field in your controller**

To check your reCAPTCHA challenge answare you add to action in your controller something like this:

```php

	if ($this->request->data && Recaptcha::check($this->request)) {
		// Do some stuff for users that passed reCAPTCHA check
	}

```

## Testing

There is not unit tests for this plugin because reCAPTCHA does not provide testing API