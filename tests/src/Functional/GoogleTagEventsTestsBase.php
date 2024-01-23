<?php

namespace Drupal\Tests\google_tag_events\Functional;

use Drupal\google_tag_events\Form\SettingsForm;
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
    $this->config(SettingsForm::CONFIG_NAME)
      ->set('debug_mode', TRUE)
      ->save();
  }

}
