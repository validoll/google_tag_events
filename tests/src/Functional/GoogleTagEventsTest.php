<?php

namespace Drupal\Tests\google_tag_events\Functional;

/**
 * Tests GTM Events.
 *
 * @group google_tag_events
 */
class GoogleTagEventsTest extends GoogleTagEventsTestsBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'google_tag',
    'google_tag_events',
    'gtm_events_test',
  ];

  /**
   * Test 404 page.
   */
  public function testTheSameEventMultipleTimes() {
    $this->drupalGet('<front>');
    $this->assertSession()->elementContains('css', 'script[data-selector="google_tag_events"]', '"gtm_events_test_front":{"event":"event_1"}');
    $this->assertSession()->elementContains('css', 'script[data-selector="google_tag_events"]', '"gtm_events_test_front_1":{"event":"event_2"}');
  }

}
