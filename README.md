# Nonce PHP

Fast PHP nonce and CSRF tokens tool, add tokens to your web forms and validate nonces easily using PHP $_SESSION (or anything else).

## Basic Usage

First, import and initialize the nonce utility class:

```php
// nonce configuration class
$nonceConfig = new \Nonce\Config\Config;

// initialize nonce class
$nonceUtil = new \Nonce\Nonce( $nonceConfig );
```

Then, to create a nonce based on an action name:

```php
// make sure you make this call before starting the output or sending HTTP headers
$nonce = $nonceUtil->create( 'signup-form' );
```

Here you see we used the `signup-form` as an action name and we can use that later to verify the nonce supplied to the user request:

Let's use this in our HTML form:

```html
<form method="post">
    ....
    ....

    <input type="hidden" name="nonce" value="<?php echo htmlentities($nonce); ?>" />
</form>

```

Now the form should appear something like this on the front-end (i.e with the nonce field added):

```html
<form method="post">
    ....
    ....

    <input type="hidden" name="nonce" value="7ad510a2296535d545615d" />
</form>
```

To verify the nonce for this form on submission, we can pass the `nonce` hash to the method `$nonceUtil->verify( string $hash, string $action )`:

```php
if ( isset( $_POST['nonce'] ) && $nonceUtil->verify( $_POST['nonce'], 'signup-form' ) ) {
    # nonce is valid
}
```

## Configuration

When initializing the `Nonce\Nonce` class, you're passing the config class as a first argument:

```php
// nonce configuration class
$nonceConfig = new \Nonce\Config\Config;
```

You can customize the default configs by calling the `$nonceConfig->setConfig` method or by passing your own config class which implements `Nonce\Config\Base` interface.

```php
$nonceConfig->setConfig( string $config_name, $config_value );
```

This allows you to overwrite the default constants of the config class.

For example, to update the cookie settings:

```php
$nonceConfig->setConfig( 'RANDOM_SALT', '@ny-ch4r4cth€r' );
$nonceConfig->setConfig( 'TOKEN_HASHER_ALGO', 'sha256' );
```

### Available config constants

Remember to use `$nonceConfig->setConfig` to update any of the following config keys:

```php
$nonceConfig::RANDOM_SALT = 'HI5CTp$94deNBCUqIQx63Z8P$T&^_z`dy';
```

```php
$nonceConfig::TOKEN_HASHER_ALGO = 'sha512';
```

Which algo should be passed to [`hash`](http://php.net/manual/en/function.hash.php) to generate a token.

## Hash store drivers

The nonces identifier data needs to be stored temporarily to be used for later verification.

### Your Own

You can use any other means of temporary data stores, by passing a class which implements the `\Nonce\HashStore\Store` interface:

```php
<?php

class CustomStore implements \Nonce\HashStore\Store
{
    /**
      * Store a key temporarily
      *
      * @param string $name key to be stored
      * @param string $value value to be stored for the given key
      * @param int $expire_seconds expire the data after X seconds (data TTL)
      * @return bool success/failure
      */

    public function setKey( string $name, string $value, int $expire_seconds=0 ) : bool
    {
        // ...
    }

    /**
      * Get a key from temporary storage
      *
      * @param string $name key to be retrieved
      * @return string value for stored key or empty string on key unavailable
      */

    public function getKey( string $name ) : string
    {
        // ...
    }

    /**
      * Unset a key from temporary storage
      *
      * @param string $name key to be removed
      * @return bool success/failure
      */

    public function deleteKey( string $name ) : bool
    {
        // ...
    }
}
```

## Credits

This work was forked from elhardoum/nonce-php at <https://github.com/elhardoum/nonce-php>
A lot of this work was inspired by the article of Simon Ugorji at <https://medium.com/nerd-for-tech/how-to-create-a-simple-nonce-in-php-a5afe046beee>
