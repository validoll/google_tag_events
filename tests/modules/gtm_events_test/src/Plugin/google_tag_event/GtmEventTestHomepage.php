<?php

namespace Drupal\gtm_events_test\Plugin\google_tag_event;

use Drupal\google_tag_events\GoogleTagEventsPluginBase;

/**
 * Homepage visit GTM event plugin.
 *
 * @Plugin(
 *   id = "gtm_events_test_homepage",
 *   label = @Translation("Homepage test event")
 * )
 */
class GtmEventTestHomepage extends GoogleTagEventsPluginBase {

  /**
   * {@inheritdoc}
   */
  public function process(array $data = NULL) {
    $data = $data ?? $this->data;

    $data += [
      'event' => 'homepage',
    ];

    return $data;
  }

}
