# ![Logo](https://raw.githubusercontent.com/RobThree/TwoFactorAuth/master/logo.png) PHP library for Two Factor Authentication

[![Build status](https://img.shields.io/travis/RobThree/TwoFactorAuth.svg?style=flat-square)](https://travis-ci.org/RobThree/TwoFactorAuth/) [![Latest Stable Version](https://img.shields.io/packagist/v/robthree/twofactorauth.svg?style=flat-square)](https://packagist.org/packages/robthree/twofactorauth) [![License](https://img.shields.io/packagist/l/robthree/twofactorauth.svg?style=flat-square)](LICENSE) [![Downloads](https://img.shields.io/packagist/dt/robthree/twofactorauth.svg?style=flat-square)](https://packagist.org/packages/robthree/twofactorauth) [![Code Climate](https://img.shields.io/codeclimate/github/RobThree/TwoFactorAuth.svg?style=flat-square)](https://codeclimate.com/github/RobThree/TwoFactorAuth) [![PayPal donate button](http://img.shields.io/badge/paypal-donate-orange.svg?style=flat-square)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6MB5M2SQLP636 "Keep me off the streets")

PHP library for [two-factor (or multi-factor) authentication](http://en.wikipedia.org/wiki/Multi-factor_authentication) using [TOTP](http://en.wikipedia.org/wiki/Time-based_One-time_Password_Algorithm) and [QR-codes](http://en.wikipedia.org/wiki/QR_code). Inspired by, based on but most importantly an *improvement* on '[PHPGangsta/GoogleAuthenticator](https://github.com/PHPGangsta/GoogleAuthenticator)'. There's a [.Net implementation](https://github.com/RobThree/TwoFactorAuth.Net) of this library as well.

<p align="center">
<img src="https://raw.githubusercontent.com/RobThree/TwoFactorAuth/master/multifactorauthforeveryone.png">
</p>

## Requirements

* Tested on PHP 5.6 up to 7.4
* [cURL](http://php.net/manual/en/book.curl.php) when using the provided `ImageChartsQRCodeProvider` (default), `QRServerProvider` or `QRicketProvider` but you can also provide your own QR-code provider.
* [random_bytes()](http://php.net/manual/en/function.random-bytes.php), [MCrypt](http://php.net/manual/en/book.mcrypt.php), [OpenSSL](http://php.net/manual/en/book.openssl.php) or [Hash](http://php.net/manual/en/book.hash.php) depending on which built-in RNG you use (TwoFactorAuth will try to 'autodetect' and use the best available); however: feel free to provide your own (CS)RNG.

## Installation

Run the following command:

`php composer.phar require robthree/twofactorauth`

## Quick start

If you want to hit the ground running then have a look at the [demo](demo/demo.php). It's very simple and easy!

## Usage

Here are some code snippets that should help you get started...

````php
// Create a TwoFactorAuth instance
$tfa = new RobThree\Auth\TwoFactorAuth('My Company');
````

The TwoFactorAuth class constructor accepts 7 arguments (all optional):

Argument          | Default value | Use 
------------------|---------------|--------------------------------------------------
`$issuer`         | `null`        | Will be displayed in the app as issuer name
`$digits`         | `6`           | The number of digits the resulting codes will be
`$period`         | `30`          | The number of seconds a code will be valid
`$algorithm`      | `sha1`        | The algorithm used
`$qrcodeprovider` | `null`        | QR-code provider (more on this later)
`$rngprovider`    | `null`        | Random Number Generator provider (more on this later)
`$timeprovider`   | `null`        | Time provider (more on this later)

These arguments are all '`write once`'; the class will, for it's lifetime, use these values when generating / calculating codes. The number of digits, the period and algorithm are all set to values Google's Authticator app uses (and supports). You may specify `8` digits, a period of `45` seconds and the `sha256` algorithm but the authenticator app (be it Google's implementation, Authy or any other app) may or may not support these values. Your mileage may vary; keep it on the safe side if you don't control which app your audience uses.

### Step 1: Set up secret shared key

When a user wants to setup two-factor auth (or, more correctly, multi-factor auth) you need to create a secret. This will be your **shared secret**. This secret will need to be entered by the user in their app. This can be done manually, in which case you simply display the secret and have the user type it in the app:

````php
$secret = $tfa->createSecret();
````

The `createSecret()` method accepts two arguments: `$bits` (default: `80`) and `$requirecryptosecure` (default: `true`). The former is the number of bits generated for the shared secret. Make sure this argument is a multiple of 8 and, again, keep in mind that not all combinations may be supported by all apps. Google authenticator seems happy with 80 and 160, the default is set to 80 because that's what most sites (that I know of) currently use; however a value of 160 or higher is recommended (see [RFC 4226 - Algorithm Requirements](https://tools.ietf.org/html/rfc4226#section-4)). The latter is used to ensure that the secret is cryptographically secure; if you don't care very much for cryptographically secure secrets you can specify `false` and use a **non**-cryptographically secure RNG provider.

````php
// Display shared secret
<p>Please enter the following code in your app: '<?php echo $secret; ?>'</p>
````

Another, more user-friendly, way to get the shared secret into the app is to generate a [QR-code](http://en.wikipedia.org/wiki/QR_code) which can be scanned by the app. To generate these QR codes you can use any one of the built-in `QRProvider` classes:

1. `ImageChartsQRCodeProvider` (default)
2. `QRServerProvider`
3. `QRicketProvider`

...or implement your own provider. To implement your own provider all you need to do is implement the `IQRCodeProvider` interface. You can use the built-in providers mentioned before to serve as an example or read the next chapter in this file. The built-in classes all use a 3rd (e.g. external) party (Image-charts, QRServer and QRicket) for the hard work of generating QR-codes (note: each of these services might at some point not be available or impose limitations to the number of codes generated per day, hour etc.). You could, however, easily use a project like [PHP QR Code](http://phpqrcode.sourceforge.net/) (or one of the [many others](https://packagist.org/search/?q=qr)) to generate your QR-codes without depending on external sources. Later on we'll [demonstrate](#qr-code-providers) how to do this.

The built-in providers all have some provider-specific 'tweaks' you can 'apply'. Some provide support for different colors, others may let you specify the desired image-format etc. What they all have in common is that they return a QR-code as binary blob which, in turn, will be turned into a [data URI](http://en.wikipedia.org/wiki/Data_URI_scheme) by the `TwoFactorAuth` class. This makes it easy for you to display the image without requiring extra 'roundtrips' from browser to server and vice versa.

````php
// Display QR code to user
<p>Scan the following image with your app:</p>
<p><img src="<?php echo $tfa->getQRCodeImageAsDataUri('Bob Ross', $secret); ?>"></p>
````

When outputting a QR-code you can choose a `$label` for the user (which, when entering a shared secret manually, will have to be chosen by the user). This label may be an empty string or `null`. Also a `$size` may be specified (in pixels, width == height) for which we use a default value of `200`.

### Step 2: Verify secret shared key

When the shared secret is added to the app, the app will be ready to start generating codes which 'expire' each '`$period`' number of seconds. To make sure the secret was entered, or scanned, correctly you need to verify this by having the user enter a generated code. To check if the generated code is valid you call the `verifyCode()` method:

````php
// Verify code
$result = $tfa->verifyCode($_SESSION['secret'], $_POST['verification']);
````

`verifyCode()` will return either `true` (the code was valid) or `false` (the code was invalid; no points for you!). You may need to store `$secret` in a `$_SESSION` or other persistent storage between requests. The `verifyCode()` accepts, aside from `$secret` and `$code`, three more arguments. The first being `$discrepancy`. Since TOTP codes are based on time("slices") it is very important that the server (but also client) have a correct date/time. But because the two *may* differ a bit we usually allow a certain amount of leeway. Because generated codes are valid for a specific period (remember the `$period` argument in the `TwoFactorAuth`'s constructor?) we usually check the period directly before and the period directly after the current time when validating codes. So when the current time is `14:34:21`, which results in a 'current timeslice' of `14:34:00` to `14:34:30` we also calculate/verify the codes for `14:33:30` to `14:34:00` and for `14:34:30` to `14:35:00`. This gives us a 'window' of `14:33:30` to `14:35:00`. The `$discrepancy` argument specifies how many periods (or: timeslices) we check in either direction of the current time. The default `$discrepancy` of `1` results in (max.) 3 period checks: -1, current and +1 period. A `$discrepancy` of `4` would result in a larger window (or: bigger time difference between client and server) of -4, -3, -2, -1, current, +1, +2, +3 and +4 periods.

The second, `$time`, allows you to check a code for a specific point in time. This argument has no real practical use but can be handy for unittesting etc. The default value, `null`, means: use the current time.

The third, `$timeslice`, is an out-argument; the value returned in `$timeslice` is the value of the timeslice that matched the code (if any). This value will be 0 when the code doesn't match and non-zero when the code matches. This value can be stored with the user and can be used to prevent replay-attacks. All you need to do is, on successful login, make sure `$timeslice` is greater than the previously stored timeslice.

### Step 3: Store `$secret` with user and we're done!

Ok, so now the code has been verified and found to be correct. Now we can store the `$secret` with our user in our database (or elsewhere) and whenever the user begins a new session we ask for a code generated by the authentication app of their choice. All we need to do is call `verifyCode()` again with the shared secret and the entered code and we know if the user is legit or not.

Simple as 1-2-3.

All we need is 3 methods and a constructor:

````php
public function __construct(
    $issuer = null, 
    $digits = 6,
    $period = 30, 
    $algorithm = 'sha1', 
    RobThree\Auth\Providers\Qr\IQRCodeProvider $qrcodeprovider = null,
    RobThree\Auth\Providers\Rng\IRNGProvider $rngprovider = null
);
public function createSecret($bits = 80, $requirecryptosecure = true): string;
public function getQRCodeImageAsDataUri($label, $secret, $size = 200): string;
public function verifyCode($secret, $code, $discrepancy = 1, $time = null): bool;
````

### QR-code providers

As mentioned before, this library comes with three 'built-in' QR-code providers. This chapter will touch the subject a bit but most of it should be self-explanatory. The `TwoFactorAuth`-class accepts a `$qrcodeprovider` argument which lets you specify a built-in or custom QR-code provider. All three built-in providers do a simple HTTP request to retrieve an image using cURL and implement the [`IQRCodeProvider`](lib/Providers/Qr/IQRCodeProvider.php) interface which is all you need to implement to write your own QR-code provider.

The default provider is the [`ImageChartsQRCodeProvider`](lib/Providers/Qr/ImageChartsQRCodeProvider.php) which uses the [image-charts.com replacement for Google Image Charts](https://image-charts.com) to render QR-codes. Then we have the [`QRServerProvider`](lib/Providers/Qr/QRServerProvider.php) which uses the [goqr.me API](http://goqr.me/api/doc/create-qr-code/) and finally we have the [`QRicketProvider`](lib/Providers/Qr/QRicketProvider.php) which uses the [QRickit API](http://qrickit.com/qrickit_apps/qrickit_api.php). All three inherit from a common (abstract) baseclass named [`BaseHTTPQRCodeProvider`](lib/Providers/Qr/BaseHTTPQRCodeProvider.php) because all three share the same functionality: retrieve an image from a 3rd party over HTTP. All three classes have constructors that allow you to tweak some settings and most, if not all, arguments should speak for themselves. If you're not sure which values are supported, click the links in this paragraph for documentation on the API's that are utilized by these classes.

If you don't like any of the built-in classes because you don't want to rely on external resources for example or because you're paranoid about sending the TOTP secret to these 3rd parties (which is useless to them since they miss *at least one* other factor in the [MFA process](http://en.wikipedia.org/wiki/Multi-factor_authentication)), feel tree to implement your own. The `IQRCodeProvider` interface couldn't be any simpler. All you need to do is implement 2 methods:

````php
getMimeType();
getQRCodeImage($qrtext, $size);
````

The `getMimeType()` method should return the [MIME type](http://en.wikipedia.org/wiki/Internet_media_type) of the image that is returned by our implementation of `getQRCodeImage()`. In this example it's simply `image/png`. The `getQRCodeImage()` method is passed two arguments: `$qrtext` and `$size`. The latter, `$size`, is simply the width/height in pixels of the image desired by the caller. The first, `$qrtext` is the text that should be encoded in the QR-code. An example of such a text would be:

`otpauth://totp/LABEL:alice@google.com?secret=JBSWY3DPEHPK3PXP&issuer=ISSUER`

All you need to do is return the QR-code as binary image data and you're done. All parts of the `$qrtext` have been escaped for you (but note: you *may* need to escape the entire `$qrtext` just once more when passing the data to another server as GET-argument).

Let's see if we can use [PHP QR Code](http://phpqrcode.sourceforge.net/) to implement our own, custom, no-3rd-parties-allowed-here, provider. We start with downloading the [required (single) file](https://github.com/t0k4rt/phpqrcode/blob/master/phpqrcode.php) and putting it in the directory where `TwoFactorAuth.php` is located as well. Now let's implement the provider: create another file named `myprovider.php` in the `Providers\Qr` directory and paste in this content:

````php
<?php
require_once '../../phpqrcode.php';                 // Yeah, we're gonna need that

namespace RobThree\Auth\Providers\Qr;

class MyProvider implements IQRCodeProvider {
  public function getMimeType() {
    return 'image/png';                             // This provider only returns PNG's
  }
  
  public function getQRCodeImage($qrtext, $size) {
    ob_start();                                     // 'Catch' QRCode's output
    QRCode::png($qrtext, null, QR_ECLEVEL_L, 3, 4); // We ignore $size and set it to 3
                                                    // since phpqrcode doesn't support
                                                    // a size in pixels...
    $result = ob_get_contents();                    // 'Catch' QRCode's output
    ob_end_clean();                                 // Cleanup
    return $result;                                 // Return image
  }
}
````

That's it. We're done! We've implemented our own provider (with help of PHP QR Code). No more external dependencies, no more unnecessary latencies. Now let's *use* our provider:

````php
<?php
$mp = new RobThree\Auth\Providers\Qr\MyProvider();
$tfa = new RobThree\Auth\TwoFactorAuth('My Company', 6, 30, 'sha1', $mp);
$secret = $tfa->createSecret();
?>
<p><img src="<?php echo $tfa->getQRCodeImageAsDataUri('Bob Ross', $secret); ?>"></p>
````

Voilà. Couldn't make it any simpler.

### RNG providers

This library also comes with three 'built-in' RNG providers ([Random Number Generator](https://en.wikipedia.org/wiki/Random_number_generation)). The RNG provider generates a number of random bytes and returns these bytes as a string. These values are then used to create the secret. By default (no RNG provider specified) TwoFactorAuth will try to determine the best available RNG provider to use. It will, by default, try to use the [`CSRNGProvider`](lib/Providers/Rng/CSRNGProvider.php) for PHP7+ or the [`MCryptRNGProvider`](lib/Providers/Rng/MCryptRNGProvider.php); if this is not available/supported for any reason it will try to use the [`OpenSSLRNGProvider`](lib/Providers/Rng/OpenSSLRNGProvider.php) and if that is also not available/supported it will try to use the final RNG provider: [`HashRNGProvider`](lib/Providers/Rng/HashRNGProvider.php). Each of these providers use their own method of generating a random sequence of bytes. The first three (`CSRNGProvider`, `OpenSSLRNGProvider` and `MCryptRNGProvider`) return a [cryptographically secure](https://en.wikipedia.org/wiki/Cryptographically_secure_pseudorandom_number_generator) sequence of random bytes whereas the `HashRNGProvider` returns a **non-cryptographically secure** sequence.

You can easily implement your own `RNGProvider` by simply implementing the `IRNGProvider` interface. Each of the 'built-in' RNG providers have some constructor arguments that allow you to 'tweak' some of the settings to use when creating the random bytes such as which source to use (`MCryptRNGProvider`) or which hashing algorithm (`HashRNGProvider`). I encourage you to have a look at some of the ['built-in' RNG providers](lib/Providers/Rng) for details and the [`IRNGProvider` interface](lib/Providers/Rng/IRNGProvider.php).

### Time providers

Another set of providers in this library are the Time Providers; this library provides three 'built-in' ones. The default Time Provider used is the [`LocalMachineTimeProvider`](lib/Providers/Time/LocalMachineTimeProvider.php); this provider simply returns the output of `Time()` and is *highly recommended* as default provider. The [`HttpTimeProvider`](lib/Providers/Time/HttpTimeProvider.php) executes a `HEAD` request against a given webserver (default: google.com) and tries to extract the `Date:`-HTTP header and returns it's date. Other url's/domains can be used by specifying the url in the constructor. The final Time Provider is the [`NTPTimeProvider`](lib/Providers/Time/NTPTimeProvider.php) which does an NTP request to a specified NTP server.

You can easily implement your own `TimeProvider` by simply implementing the `ITimeProvider` interface.

As to *why* these Time Providers are implemented: it allows the TwoFactorAuth library to ensure the hosts time is correct (or rather: within a margin). You can use the `ensureCorrectTime()` method to ensure the hosts time is correct. By default this method will compare the hosts time (returned by calling `time()` on the `LocalMachineTimeProvider`) to the default `NTPTimeProvider` and `HttpTimeProvider`. You can pass an array of `ITimeProvider`s to change this and specify the `leniency` (second argument) allowed (default: 5 seconds). The method will throw when the TwoFactorAuth's timeprovider (which can be any `ITimeProvider`, see constructor) differs more than the given amount of seconds from any of the given `ITimeProviders`. We advise to call this method sparingly when relying on 3rd parties (which both the `HttpTimeProvider` and `NTPTimeProvider` do) or, if you need to ensure time is correct on a (very) regular basis to implement an `ITimeProvider` that is more efficient than the 'built-in' ones (like use a GPS signal). The `ensureCorrectTime()` method is mostly to be used to make sure the server is configured correctly.

## Integrations

- [CakePHP 3](https://github.com/andrej-griniuk/cakephp-two-factor-auth) 

## License

Licensed under MIT license. See [LICENSE](https://raw.githubusercontent.com/RobThree/TwoFactorAuth/master/LICENSE) for details.

[Logo / icon](http://www.iconmay.com/Simple/Travel_and_Tourism_Part_2/luggage_lock_safety_baggage_keys_cylinder_lock_hotel_travel_tourism_luggage_lock_icon_465) under  CC0 1.0 Universal (CC0 1.0) Public Domain Dedication  ([Archived page](http://riii.nl/tm7ap))
