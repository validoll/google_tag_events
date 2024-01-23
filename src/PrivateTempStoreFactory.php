<?php

namespace Drupal\google_tag_events;

use Drupal\Core\KeyValueStore\KeyValueExpirableFactoryInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\Core\TempStore\PrivateTempStoreFactory as CorePrivateTempStoreFactory;
use Drupal\Core\TempStore\TempStoreException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Creates a PrivateTempStore object.
 */
class PrivateTempStoreFactory extends CorePrivateTempStoreFactory {

  /**
   * The logger instance.
   */
  protected LoggerInterface $logger;

  /**
   * Private temp store object class.
   */
  protected string $privateTempStoreClass;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    KeyValueExpirableFactoryInterface $storage_factory,
    LockBackendInterface $lock_backend,
    AccountProxyInterface $current_user,
    RequestStack $request_stack,
                                      $expire = 604800,
    LoggerInterface $logger = NULL,
                                      $private_temp_store_class = PrivateTempStoreCookie::class
  ) {
    parent::__construct($storage_factory, $lock_backend, $current_user, $request_stack, $expire);

    // Allow to override Private Temp Store class.
    $this->privateTempStoreClass = $private_temp_store_class;

    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public function get($collection) {
    // Store the data for this collection in the database.
    $storage = $this->storageFactory->get("tempstore.private.$collection");
    $cookie_temp_store = new $this->privateTempStoreClass($storage, $this->lockBackend, $this->currentUser, $this->requestStack, $this->expire);

    if ($this->currentUser->isAnonymous()) {
      return $cookie_temp_store;
    }
    else {
      $temp_store = new PrivateTempStore($storage, $this->lockBackend, $this->currentUser, $this->requestStack, $this->expire);
      $cookie_temp_store_value = unserialize((string) $cookie_temp_store->get($collection), ['allowed_classes' => FALSE]) ?: [];

      if (empty($cookie_temp_store_value)) {
        return $temp_store;
      }

      // Move data fom cookie to temp store.
      $temp_store_value = unserialize((string) $temp_store->get($collection), ['allowed_classes' => FALSE]) ?: [];
      $temp_store_value += $cookie_temp_store_value;

      try {
        $temp_store->set($collection, serialize($temp_store_value));
      }
      catch (TempStoreException $e) {
        $this->logger->error($e->getMessage());
      }
      finally {
        $cookie_temp_store->deleteAll();
      }

      return $temp_store;
    }
  }

}
