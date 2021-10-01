<?php

namespace Drupal\google_tag_events;

use Drupal\Core\TempStore\PrivateTempStore;

/**
 * Stores and retrieves temporary data for a given owner.
 */
class PrivateTempStoreCookie extends PrivateTempStore {

  const COOKIE_PREFIX = 'gte_ptsc_';

  /**
   * Retrieves a value from this PrivateTempStore for a given key.
   *
   * @param string $key
   *   The key of the data to retrieve.
   *
   * @return mixed
   *   The data associated with the key, or NULL if the key does not exist.
   */
  public function get($key) {
    $key = static::COOKIE_PREFIX . $key;

    return $_COOKIE[$key] ?? NULL;
  }

  /**
   * Stores a particular key/value pair in this PrivateTempStore.
   *
   * @param string $key
   *   The key of the data to store.
   * @param mixed $value
   *   The data to store.
   */
  public function set($key, $value) {
    if (empty($value)) {
      $this->delete($key);
      return;
    }
    $key = static::COOKIE_PREFIX . $key;
    $params = session_get_cookie_params();
    $expire_time = $this->requestStack->getCurrentRequest()->server->get('REQUEST_TIME') + $this->expire;
    setcookie($key, $value, $expire_time, $params['path'], $params['domain']);
    $_COOKIE[$key] = $value;
  }

  /**
   * Deletes data from the store for a given key and releases the lock on it.
   */
  public function delete($key) {
    $key = static::COOKIE_PREFIX . $key;
    $params = session_get_cookie_params();
    setcookie($key, NULL, -1, $params['path'], $params['domain']);
    unset($_COOKIE[$key]);
  }

  /**
   * Deletes all PrivateTempStoreCookie cookies.
   */
  public function deleteAll() {
    $params = session_get_cookie_params();
    foreach ($_COOKIE as $key => $cookie) {
      if (strpos($key, static::COOKIE_PREFIX) === 0) {
        setcookie($key, NULL, -1, $params['path'], $params['domain']);
        unset($_COOKIE[$key]);
      }
    }
  }

}
