<?php

namespace Drupal\Tests\google_tag_events\Functional;

/**
 * Tests GTM Events for error pages.
 *
 * @group google_tag_events
 */
class GoogleTagEventsErrorPageTest extends GoogleTagEventsTestsBase {

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
  public function test404Page() {
    // Go to wrong url.
    $this->drupalGet('wrong/url');
    $this->assertSession()->statusCodeEquals(404);
    $this->assertSession()->elementContains('css', 'script[data-selector="google_tag_events"]', '{"gtm_events_test_404":{"event":"404"}}');
  }

  /**
   * Test 403 page.
   */
  public function test403Page() {
    // Admin page is not allowed for anonymous.
    $this->drupalGet('admin');
    $this->assertSession()->statusCodeEquals(403);
    $this->assertSession()->elementContains('css', 'script[data-selector="google_tag_events"]', '{"gtm_events_test_403":{"event":"403"}}');
  }

}
