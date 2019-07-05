<?php

namespace Drupal\gtm_events_test\Plugin\google_tag_event;

use Drupal\google_tag_events\GoogleTagEventsPluginBase;

/**
 * Form submit GTM event plugin.
 *
 * @Plugin(
 *   id = "gtm_events_test_form_submit",
 *   label = @Translation("Form submit test event")
 * )
 */
class GtmEventTestFormSubmit extends GoogleTagEventsPluginBase {

  /**
   * {@inheritdoc}
   */
  public function process(array $data = NULL) {
    $data = $data ?? $this->data;

    $data += [
      'event' => 'form submit',
    ];

    return $data;
  }

}
