<?php

namespace Drupal\gtm_events_test\Plugin\google_tag_event;

use Drupal\google_tag_events\GoogleTagEventsPluginBase;

/**
 * Error 403 GTM event plugin.
 *
 * @Plugin(
 *   id = "gtm_events_test_403",
 *   label = @Translation("Error 403 event")
 * )
 */
class Error403GtmEventsPlugin extends GoogleTagEventsPluginBase {

  /**
   * {@inheritdoc}
   */
  public function process(array $data = NULL) {
    $data = $data ?? $this->data;

    $data += [
      'event' => '403',
    ];

    return $data;
  }

}
