<?php

namespace Drupal\google_tag_events;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Provides GTM Events plugin manager.
 *
 * @see plugin_api
 */
class GoogleTagEventsPluginManager extends DefaultPluginManager {

  /**
   * Constructs a GoogleTagEventsPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/google_tag_event',
      $namespaces,
      $module_handler
    );
    $this->alterInfo('google_tag_events');
    $this->setCacheBackend($cache_backend, 'google_tag_events_plugins');
  }

}
