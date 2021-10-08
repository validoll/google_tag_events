<?php

namespace Drupal\google_tag_events;

use Drupal\Core\KeyValueStore\KeyValueExpirableFactoryInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory as CorePrivateTempStoreFactory;
use Drupal\Core\TempStore\PrivateTempStore;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Creates a PrivateTempStore object.
 */
class PrivateTempStoreFactory extends CorePrivateTempStoreFactory {


  /**
   * Private temp store object class.
   *
   * @var string
   */
  protected $privateTempStoreClass;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    KeyValueExpirableFactoryInterface $storage_factory,
    LockBackendInterface $lock_backend,
    AccountProxyInterface $current_user,
    RequestStack $request_stack,
    $expire = 604800,
    $private_temp_store_class = PrivateTempStoreCookie::class
  ) {
    parent::__construct($storage_factory, $lock_backend, $current_user, $request_stack, $expire);

    // Allow to override Private Temp Store class.
    $this->privateTempStoreClass = $private_temp_store_class;
  }

  /**
   * {@inheritdoc}
   */
  public function get($collection) {
    // Store the data for this collection in the database.
    $storage = $this->storageFactory->get("user.private_tempstore.$collection");
    if ($this->currentUser->isAnonymous()) {
      return new $this->privateTempStoreClass($storage, $this->lockBackend, $this->currentUser, $this->requestStack, $this->expire);
    }
    else {
      // Move data fom cookie to temp store.
      $cookie_temp_store = new $this->privateTempStoreClass($storage, $this->lockBackend, $this->currentUser, $this->requestStack, $this->expire);
      $cookie_temp_store_value = unserialize($cookie_temp_store->get($collection)) ?: [];

      $temp_store = new PrivateTempStore($storage, $this->lockBackend, $this->currentUser, $this->requestStack, $this->expire);
      $temp_store_value = unserialize($temp_store->get($collection)) ?: [];
      $temp_store_value += $cookie_temp_store_value;

      $temp_store->set($collection, serialize($temp_store_value));

      $cookie_temp_store->deleteAll();

      return $temp_store;
    }
  }

}
