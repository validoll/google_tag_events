<?php

namespace Drupal\gtm_events_test\Plugin\google_tag_event;

use Drupal\google_tag_events\GoogleTagEventsPluginBase;

/**
 * Basic page node page visit GTM event plugin.
 *
 * @Plugin(
 *   id = "gtm_events_test_page_view",
 *   label = @Translation("Page page visit event")
 * )
 */
class GtmEventTestPageView extends GoogleTagEventsPluginBase {

  /**
   * {@inheritdoc}
   */
  public function process(array $data = NULL) {
    $data = $data ?? $this->data;

    $data += [
      'event' => 'page',
    ];

    return $data;
  }

}
