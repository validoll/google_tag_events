<?php

namespace Drupal\gtm_events_test\Plugin\google_tag_event;

use Drupal\google_tag_events\GoogleTagEventsPluginBase;

/**
 * Error 404 GTM event plugin.
 *
 * @Plugin(
 *   id = "gtm_events_test_404",
 *   label = @Translation("Error 404 event")
 * )
 */
class Error404GtmEventsPlugin extends GoogleTagEventsPluginBase {

  /**
   * {@inheritdoc}
   */
  public function process(array $data = NULL) {
    $data = $data ?? $this->data;

    $data += [
      'event' => '404',
    ];

    return $data;
  }

}
