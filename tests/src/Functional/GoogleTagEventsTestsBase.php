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
  protected $defaultTheme = 'starterkit_theme';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Enable debug mode.
    $config = $this->config('google_tag_events.settings');
    $config->set('debug_mode', TRUE);
    $config->save();
  }

}
