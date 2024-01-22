/**
 * @file
 * Provide behavior of GTM tracking and tagging to test.
 */

(function ($, Drupal, once, drupalSettings) {

  'use strict';

    // GTM events tracking behavior.
    Drupal.behaviors.gtm_events_test = {
        attach: function (context, settings) {
            // Example of GTM event on anchor click.
          once('processed', "a").forEach(function () {
                $(this).on('click', function (e) {
                    drupalSettings.google_tag_events.gtmEvents['gtm_events_test_click'] = {'foo': 'bar'};
                    Drupal.attachBehaviors(this);
                });
            });
        }
    };

}(jQuery, Drupal, once, drupalSettings));
