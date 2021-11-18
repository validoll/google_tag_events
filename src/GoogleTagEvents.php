<?php

namespace Drupal\google_tag_events;

use Drupal\Core\Ajax\SettingsCommand;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Component\Serialization\Json;
use Drupal\Core\TempStore\TempStoreException;
use Drupal\google_tag\Entity\ContainerManagerInterface;
use Drupal\google_tag_events\Form\SettingsForm;
use Psr\Log\LoggerInterface;

/**
 * GTM events manager.
 *
 * @package Drupal\google_tag_events
 */
class GoogleTagEvents {

  use StringTranslationTrait;

  const TYPE = 'google_tag_events';

  /**
   * The config factory.
   *
   * Subclasses should use the self::config() method, which may be overridden to
   * address specific needs when loading config, rather than this property
   * directly. See \Drupal\Core\Form\ConfigFormBase::config() for an example of
   * this.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Temporary storage.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  protected $tempStore;

  /**
   * GTM events plugin manager.
   *
   * @var \Drupal\google_tag_events\GoogleTagEventsPluginManager
   */
  protected $pluginManager;

  /**
   * Array of current events to add.
   *
   * @var array
   */
  protected $currentEvents = [];

  /**
   * The GTM container manager.
   *
   * @var \Drupal\google_tag\Entity\ContainerManagerInterface
   */
  protected $containerManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * GoogleTagEvents constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   Temporary storage.
   * @param \Drupal\google_tag_events\GoogleTagEventsPluginManager $plugin_manager
   *   GTM events plugin manager.
   * @param \Drupal\google_tag\Entity\ContainerManagerInterface $container_manager
   *   The GTM container manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Psr\Log\LoggerInterface $entity_type_manager
   *   The logger instance.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    PrivateTempStoreFactory $temp_store_factory,
    GoogleTagEventsPluginManager $plugin_manager,
    ContainerManagerInterface $container_manager,
    EntityTypeManagerInterface $entity_type_manager,
    LoggerInterface $logger
  ) {
    $this->configFactory = $config_factory;
    $this->tempStore = $temp_store_factory->get(static::TYPE);
    $this->pluginManager = $plugin_manager;
    $this->currentEvents = unserialize($this->tempStore->get(static::TYPE) ?: 'a:0:{}');
    $this->containerManager = $container_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;
  }

  /**
   * Return Google Tag config object.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   The config object.
   */
  protected function getGoogleTagConfig() {
    return $this->configFactory->get('google_tag.settings');
  }

  /**
   * Debug mode status.
   *
   * @return bool
   *   Check debug mode status.
   */
  protected function isDebugMode() {
    return $this->configFactory->get(SettingsForm::CONFIG_NAME)->get('debug_mode');
  }

  /**
   * Get GTM status.
   *
   * @return bool
   *   GTM status.
   */
  public function gtmIsEnabled() {
    static $satisfied;

    if (!isset($satisfied)) {
      if ($this->isDebugMode()) {
        $satisfied = TRUE;

        return TRUE;
      }

      $ids = $this->containerManager->loadContainerIDs();
      $containers = $this->entityTypeManager->getStorage('google_tag_container')->loadMultiple($ids);

      foreach ($containers as $container) {
        if (!$container->access('view')) {
          continue;
        }

        $satisfied = TRUE;

        break;
      }
    }

    return $satisfied;
  }

  /**
   * GoogleTagEvents destructor.
   *
   * Use to store list of events in session.
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  public function __destruct() {
    $this->saveEvents();
  }

  /**
   * Save events to storage.
   */
  public function saveEvents() {
    try {
      $this->tempStore->set(static::TYPE, serialize($this->currentEvents));
    }
    catch (TempStoreException $e) {
      $this->logger->error($e->getMessage());
    }
  }

  /**
   * Add event to push.
   *
   * @param string $name
   *   Event name.
   * @param array $data
   *   Event data.
   * @param bool $save_to_tempstore
   *   Save data to temp storage if TRUE.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function setEvent(string $name, array $data = [], bool $save_to_tempstore = FALSE) {
    if (!$this->gtmIsEnabled()) {
      return;
    }

    // Get all plugin definitions.
    $plugin_definitions = $this->pluginManager->getDefinitions();

    if (array_key_exists($name, $plugin_definitions)) {
      /** @var \Drupal\google_tag_events\GoogleTagEventPluginInterface $plugin */
      $plugin = $this->pluginManager->createInstance($name, ['data' => $data]);
      $data = $plugin->process($data);
    }
    else {
      $data['event'] = $data['event'] ?? $name;
    }

    if (empty($data)) {
      return;
    }

    $this->currentEvents[$name] = $this->currentEvents[$name] ?? [];
    $this->currentEvents[$name] += $data;

    if ($save_to_tempstore) {
      $this->saveEvents();
    }
  }

  /**
   * Get events weights list.
   *
   * @return array
   *   List of events plugins with weight.
   */
  public function getEventsWeightsList() {
    $weights = [];

    // Get all plugin definitions.
    $plugin_definitions = $this->pluginManager->getDefinitions();

    foreach ($plugin_definitions as $name => $plugin_definitions) {
      if (empty($plugin_definitions['weight'])) {
        continue;
      }

      $weights[$name] = $plugin_definitions['weight'];
    }

    return $weights;
  }

  /**
   * Get list of current events.
   *
   * @return array
   *   Array of curretn events.
   */
  public function getEvents() {
    return $this->currentEvents;
  }

  /**
   * Flush all current events.
   */
  public function flushEvents() {
    $this->currentEvents = [];
    $this->tempStore->delete(static::TYPE);
  }

  /**
   * Attach events to page.
   *
   * @param array $build
   *   Page or element build array.
   */
  public function processCurrentEvents(array &$build) {
    $grmEventsDrupalSettings = &$build['#attached']['drupalSettings'][static::TYPE];

    $grmEventsDrupalSettings['enabled'] = $this->gtmIsEnabled();
    $grmEventsDrupalSettings['weights'] = $this->getEventsWeightsList();
  }

  /**
   * Attach events to page during ajax call.
   */
  public function processAjaxCommandCurrentEvents() {
    $setting[static::TYPE] = [
      'enabled' => $this->gtmIsEnabled(),
    ];

    foreach ($this->currentEvents as $event => $data) {
      $setting[static::TYPE]['gtmEvents'][$event] = $data;

      unset($this->currentEvents[$event]);
    }
    return new SettingsCommand($setting, TRUE);
  }

  /**
   * Attach events to a page via inline script.
   */
  public function processInlineCurrentEvents() {
    // Add events to drupal settings.
    $events = $this->processAjaxCommandCurrentEvents()->render();

    if (empty($events['settings'])) {
      return '';
    }

    // Update drupalSettings to call event.
    $settings = '<script type="text/javascript">(function ($) {$.extend(drupalSettings, ';
    $settings .= Json::encode($events['settings']);
    $settings .= '); Drupal.attachBehaviors(document, drupalSettings)}(jQuery));</script>';

    return $settings;
  }

}
