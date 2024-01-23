<?php

namespace Drupal\google_tag_events;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Lazy builder callback class.
 *
 * @package Drupal\google_tag_events
 */
class LazyBuilder implements TrustedCallbackInterface {

  /**
   * The Google Tag Events service object.
   */
  protected GoogleTagEvents $googleTagEvents;

  /**
   * Lazy builder constructor.
   *
   * @param \Drupal\google_tag_events\GoogleTagEvents $google_tag_events_service
   *   The Google Tag Events service object.
   */
  public function __construct(GoogleTagEvents $google_tag_events_service) {
    $this->googleTagEvents = $google_tag_events_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['getEvents'];
  }

  /**
   * Render GTM events.
   *
   * @return array
   *   Render array.
   */
  public function getEvents() {
    $events = $this->googleTagEvents->getEvents();

    if (empty($events)) {
      return [];
    }

    $this->googleTagEvents->flushEvents();

    $drupal_settings = 'drupalSettings.' . GoogleTagEvents::TYPE;
    $status = $this->googleTagEvents->gtmIsEnabled();
    $weights = Json::encode($this->googleTagEvents->getEventsWeightsList());

    // Init GTM Events settings on front end and add events.
    return [
      '#type' => 'html_tag',
      '#tag' => 'div',
      'value' => [
        [
          '#type' => 'html_tag',
          '#tag' => 'script',
          '#value' => "document.addEventListener('DOMContentLoaded', function(event) {"
          . "{$drupal_settings} = {$drupal_settings} || {};"
          . "{$drupal_settings}.gtmEvents = {$drupal_settings}.gtmEvents || {};"
          . "{$drupal_settings}.enabled = {$drupal_settings}.enabled || {$status};"
          . "{$drupal_settings}.weights = {$drupal_settings}.weights || {$weights};"
          . "})",
        ],
        [
          '#type' => 'html_tag',
          '#tag' => 'script',
          '#attributes' => [
            'type' => 'application/json',
            'data-selector' => 'google_tag_events',
          ],
          '#value' => Json::encode($events),
        ],
      ],
    ];
  }

}
