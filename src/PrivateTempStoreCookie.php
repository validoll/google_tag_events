<?php

namespace Drupal\google_tag_events;

use Drupal\Core\TempStore\PrivateTempStore;

/**
 * Stores and retrieves temporary data for a given owner.
 */
class PrivateTempStoreCookie extends PrivateTempStore {

  /**
   * Use this prefix to determine GTE cookie value.
   */
  const COOKIE_PREFIX = 'STYXKEY_gte_ptsc_';

  /**
   * {@inheritDoc}
   */
  protected function createkey($key) {
    return self::COOKIE_PREFIX . $key;
  }

  /**
   * {@inheritDoc}
   */
  public function get($key) {
    $key = $this->createkey($key);

    return $_COOKIE[$key] ?? NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function set($key, $value) {
    // Skip cookie modifying if headrs already sent.
    if (headers_sent()) {
      return;
    }

    if (empty($value)) {
      $this->delete($key);

      return;
    }

    $key = $this->createkey($key);
    $params = session_get_cookie_params();
    $expire_time = $this->requestStack->getCurrentRequest()->server->get('REQUEST_TIME') + $this->expire;
    setcookie($key, $value, $expire_time, $params['path'], $params['domain'], TRUE);
    $_COOKIE[$key] = $value;
  }

  /**
   * {@inheritDoc}
   */
  public function delete($key) {
    // Skip cookie modifying if headrs already sent.
    if (headers_sent()) {
      return;
    }

    $key = $this->createkey($key);
    $params = session_get_cookie_params();
    setcookie($key, '', -1, $params['path'], $params['domain'], TRUE);
    unset($_COOKIE[$key]);
  }

  /**
   * Deletes all PrivateTempStoreCookie cookies.
   */
  public function deleteAll() {
    // Skip cookie modifying if headrs already sent.
    if (headers_sent()) {
      return;
    }

    $params = session_get_cookie_params();
    foreach ($_COOKIE as $key => $cookie) {
      if (strpos($key, static::COOKIE_PREFIX) !== FALSE) {
        setcookie($key, '', -1, $params['path'], $params['domain'], TRUE);
        unset($_COOKIE[$key]);
      }
    }
  }

}
