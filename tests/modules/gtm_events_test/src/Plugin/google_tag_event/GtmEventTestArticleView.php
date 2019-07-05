<?php

namespace Drupal\gtm_events_test\Plugin\google_tag_event;

use Drupal\google_tag_events\GoogleTagEventsPluginBase;

/**
 * Article node page visit GTM event plugin.
 *
 * @Plugin(
 *   id = "gtm_events_test_article_view",
 *   label = @Translation("Article page visit event")
 * )
 */
class GtmEventTestArticleView extends GoogleTagEventsPluginBase {

  /**
   * {@inheritdoc}
   */
  public function process(array $data = NULL) {
    $data = $data ?? $this->data;

    $data += [
      'event' => 'article',
    ];

    return $data;
  }

}
