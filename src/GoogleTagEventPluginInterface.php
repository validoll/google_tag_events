<?php

namespace Drupal\google_tag_events;

/**
 * GTM Event plugin interface.
 *
 * @package Drupal\google_tag_events
 */
interface GoogleTagEventPluginInterface {

  /**
   * Process event data.
   *
   * @param array $data
   *   Array of raw data.
   *
   * @return mixed
   *   Processed data of GTM event to push.
   */
  public function process(array $data = NULL);

}
