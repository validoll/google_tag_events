<?php

namespace Drupal\google_tag_events;

use Drupal\Core\TempStore\PrivateTempStoreFactory as CorePrivateTempStoreFactory;
use Drupal\Core\TempStore\PrivateTempStore;

/**
 * Creates a PrivateTempStore object.
 */
class PrivateTempStoreFactory extends CorePrivateTempStoreFactory {

  /**
   * Creates a PrivateTempStore.
   *
   * @param string $collection
   *   The collection name to use for this key/value store. This is typically
   *   a shared namespace or module name, e.g. 'views', 'entity', etc.
   *
   * @return \Drupal\Core\TempStore\PrivateTempStore
   *   An instance of the key/value store.
   */
  public function get($collection) {
    // Store the data for this collection in the database.
    $storage = $this->storageFactory->get("user.private_tempstore.$collection");
    if ($this->currentUser->isAnonymous()) {
      return new PrivateTempStoreCookie($storage, $this->lockBackend, $this->currentUser, $this->requestStack, $this->expire);
    }
    else {
      // Move data fom cookie to temp store.
      $cookie_temp_store = new PrivateTempStoreCookie($storage, $this->lockBackend, $this->currentUser, $this->requestStack, $this->expire);
      $temp_store = new PrivateTempStore($storage, $this->lockBackend, $this->currentUser, $this->requestStack, $this->expire);
      $temp_store->set($collection, $cookie_temp_store->get($collection) ?? []);
      $cookie_temp_store->deleteAll();

      return $temp_store;
    }
  }

}
