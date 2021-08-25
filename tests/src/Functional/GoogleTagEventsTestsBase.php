<?php

namespace Drupal\Tests\google_tag_events\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Provides setup and helper methods for GTM Events tests.
 */
abstract class GoogleTagEventsTestsBase extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stable';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $config = $this->config('google_tag.settings');
    $config
      ->set('container_id', 'GTM-TESTKEY')
      ->set('status_list', '')
      ->save();
 }

}
