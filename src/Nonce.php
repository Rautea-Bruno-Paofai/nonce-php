<?php

namespace Nonce;

use Nonce\Config\Base as Config;
use Nonce\HashStore\Store;
use Nonce\HashStore\Session;

/**
 * Fast PHP nonce and CSRF tokens tool
 *
 * @author rautea <contact@rautea.com>
 * @version 0.1
 * @link http://github.com/rautea-bruno-paofai/nonce-php
 * @license GPL-3.0
 * @see https://github.com/rautea-bruno-paofai/nonce-php/blob/master/readme.md
 */

class Nonce
{
  private readonly Config $config;
  private readonly Store $store;

  public function __construct(Config $config, Store $store = new Session())
  {
    $this->config = $config;
    $this->store = $store;
  }

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

    return true;
  }

  public function delete(string $action)
  {
    return $this->store->deleteKey($action);
  }

  public static function getRandomCharacter(int $length = 16): string
  {
    $factory = new \RandomLib\Factory;

    $generator = $factory->getGenerator(new \SecurityLib\Strength(
      \SecurityLib\Strength::MEDIUM
    ));

    return $generator->generateString($length);
  }
}
