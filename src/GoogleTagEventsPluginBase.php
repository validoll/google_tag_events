<?php

namespace Drupal\google_tag_events;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class of GTM Events plugin.
 */
abstract class GoogleTagEventsPluginBase extends PluginBase implements ContainerFactoryPluginInterface, GoogleTagEventPluginInterface {

  /**
   * Raw data.
   */
  protected array $data = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->data = $configuration['data'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition);
  }

}
