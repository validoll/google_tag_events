/**
 * @file
 * Provide behavior of GTM tracking and tagging to test.
 */

(function ($) {

    // GTM events tracking behavior.
    Drupal.behaviors.gtm_events_test = {
        attach: function (context, settings) {
            // Example of GTM event on anchor click.
            $('a', context).once('processed').each(function () {
                $(this).on('click', function (e) {
                    drupalSettings.google_tag_events.gtmEvents['gtm_events_test_click'] = offer_info;
                    Drupal.attachBehaviors(this);
                });
            });
        }
    };

}(jQuery));
