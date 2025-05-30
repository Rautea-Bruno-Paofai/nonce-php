<?php

namespace Nonce;

use Nonce\Config\Base as Config;
use Nonce\HashStore\Store;
use Nonce\HashStore\Session;

/**
 * Fast PHP nonce and CSRF tokens tool
 *
 * @author elhardoum <i@elhardoum.com>
 * @version 0.3
 * @link http://github.com/elhardoum/nonce-php
 * @license GPL-3.0
 * @see https://github.com/elhardoum/nonce-php/blob/master/readme.md
 */

class Nonce
{
  private readonly Config $config;
  private readonly Store $store;

  /**
   * Instantiate class
   *
   * @param Config $config configuration context
   * @param Store $config temporary store context
   * @return void
   */

  public function __construct(Config $config, Store $store = new Session())
  {
    $this->config = $config;
    $this->store = $store;
  }

  /**
   * Create a nonce based on an action string
   *
   * @param string $action an action for the nonce
   * @param int $salt_length length of created salt
   * @param int $expire_seconds TTL seconds for the product hash
   * @return string generated nonce
   */

  public function create(string $action, int $salt_length = 16, int $expire_seconds = 600): string
  {
    $secret = $this->config->getConfig('RANDOM_SALT');

    //secret must be valid. You can add your regExp here
    if (false === is_string($secret) || 10 > strlen($secret)) {
      throw new \InvalidArgumentException("A valid Nonce Secret is required");
    }

    $salt = self::getRandomCharacter($salt_length);
    $time = time() + intval($expire_seconds);
    $toHash = $secret . $salt . $time;

    $algo = $this->config->getConfig('TOKEN_HASHER_ALGO');
    $nonce = $salt . ':' . $action . ':' . $time . ':' . hash($algo, $toHash);

    $this->store->setKey($action, $nonce);

    return $nonce;
  }

  /**
   * Verifies nonces authenticity and validity
   *
   * @param string $nonce nonce to be verified
   * @param string $action action name (like a password) for said nonce
   * @return bool verification outcome
   */

  public function verify(string $nonce, string $action): bool|string
  {
    $split = explode(':', $nonce);
    if (count($split) !== 4) {
      return false;
    }

    $nonce_salt = $split[0];
    $nonce_action = $split[1];
    $nonce_time = intval($split[2]);
    $nonce_hash = $split[3];

    if ($action !== $nonce_action) return false;

    if (time() > $nonce_time) return false;

    $key = $this->store->getKey($action);
    if (!$key) return false;

    if ($key != md5($nonce)) return false;

    $secret = $this->config->getConfig('RANDOM_SALT');
    $toHash = $secret . $nonce_salt . $nonce_time;
    $algo = $this->config->getConfig('TOKEN_HASHER_ALGO');
    $hash = hash($algo, $toHash);
    if ($hash !== $nonce_hash) return false;

    return self::delete($action);
  }

  /**
   * Delete a hash from temporary storage
   *
   * @param string $hash hash to be deleted
   * @return mixed implemented store return type
   */

  public function delete(string $action)
  {
    return $this->store->deleteKey($action);
  }

  /**
   * Generate a random character of X characters length
   *
   * @param int $length characters length
   * @return string generated random character
   */

  public static function getRandomCharacter(int $length = 16): string
  {
    $factory = new \RandomLib\Factory;

    $generator = $factory->getGenerator(new \SecurityLib\Strength(
      \SecurityLib\Strength::MEDIUM
    ));

    return $generator->generateString($length);
  }
}
