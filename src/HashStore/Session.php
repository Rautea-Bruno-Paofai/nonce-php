<?php

namespace Nonce\HashStore;


class Session implements Store
{
  /**
   * Store a key temporarily
   *
   * @param string $name key to be stored
   * @param string $value value to be stored for the given key
   * @param int $expire_seconds expire the data after X seconds (data TTL)
   * @return bool success/failure
   */

  public function setKey(string $name, string $value, int $expire_seconds = 0): bool
  {
    //Argument must be a string
    if (false === is_string($name)) {
      throw new \InvalidArgumentException("A valid Form ID is required");
    }

    $_SESSION['nonce'][$name] = md5($value);
    return true;
  }

  /**
   * Get a key from temporary storage
   *
   * @param string $name key to be retrieved
   * @return string value for stored key or empty string on key unavailable
   */

  public function getKey(string $name): string|bool
  {
    if (!isset($_SESSION['nonce'][$name])) {
      return false;
    }
    return (string) $_SESSION['nonce'][$name];
  }

  /**
   * Unset a key from temporary storage
   *
   * @param string $name key to be removed
   * @return bool success/failure
   */

  public function deleteKey(string $name): bool
  {
    if (!isset($_SESSION['nonce'][$name])) {
      return false;
    }
    unset($_SESSION['nonce'][$name]);
    return true;
  }
}
