<?php

namespace Drupal\google_tag_events;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\SettingsCommand;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\TempStore\TempStoreException;
use Drupal\google_tag\TagContainerResolver;
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
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * Temporary storage.
   */
  protected PrivateTempStore $tempStore;

  /**
   * GTM events plugin manager.
   */
  protected GoogleTagEventsPluginManager $pluginManager;

  /**
   * Array of current events to add.
   *
   * @var array
   */
  protected array $currentEvents = [];

  /**
   * The GTM container manager.
   *
   * @var \Drupal\google_tag\TagContainerResolver
   */
  protected $tagContainerResolver;

  /**
   * The entity type manager.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The logger instance.
   */
  protected LoggerInterface $logger;

  /**
   * GoogleTagEvents constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   Temporary storage.
   * @param \Drupal\google_tag_events\GoogleTagEventsPluginManager $plugin_manager
   *   GTM events plugin manager.
   * @param \Drupal\google_tag\TagContainerResolver $tag_container_resolver
   *   The GTM container manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger instance.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    PrivateTempStoreFactory $temp_store_factory,
    GoogleTagEventsPluginManager $plugin_manager,
    TagContainerResolver $tag_container_resolver,
    EntityTypeManagerInterface $entity_type_manager,
    LoggerInterface $logger
  ) {
    $this->configFactory = $config_factory;
    $this->tempStore = $temp_store_factory->get(static::TYPE);
    $this->pluginManager = $plugin_manager;
    $this->currentEvents = unserialize(
      (string) $this->tempStore->get(static::TYPE) ?: 'a:0:{}',
      ['allowed_classes' => FALSE]
    );
    $this->tagContainerResolver = $tag_container_resolver;
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
    $satisfied = &drupal_static(__FUNCTION__);

    if (!isset($satisfied)) {
      if ($this->isDebugMode()) {
        $satisfied = TRUE;

        return TRUE;
      }

      $resolved_tag_container = $this->tagContainerResolver->resolve();
      $satisfied = !empty($resolved_tag_container);
    }

    return $satisfied;
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
  public function setEvent(string $name, array $data = [], bool $save_to_tempstore = TRUE) {
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

    // Allow to push the same event multiple times per session.
    $key_name = $name;
    $key_index = 0;

    while (array_key_exists($key_name, $this->currentEvents)) {
      $key_index++;
      $key_name = $name . '_' . $key_index;
    }

    $this->currentEvents[$key_name] = $this->currentEvents[$key_name] ?? [];
    $this->currentEvents[$key_name] += $data;

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

    try {
      $this->tempStore->delete(static::TYPE);
    }
    catch (TempStoreException $e) {
      $this->logger->error($e->getMessage());
    }
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
    }

    // Flush events.
    $this->flushEvents();

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
