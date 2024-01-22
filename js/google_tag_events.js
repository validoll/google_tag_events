/**
 * @file
 * Provide behavior of GTM tracking and tagging.
 */

window.dataLayer = window.dataLayer || [];

(function ($, Drupal, once, drupalSettings, cookies) {

    'use strict';

    drupalSettings.google_tag_events = drupalSettings.google_tag_events || {};
    drupalSettings.google_tag_events.gtmEvents = drupalSettings.google_tag_events.gtmEvents || {};
    drupalSettings.google_tag_events.enabled = drupalSettings.google_tag_events.enabled || false;

    // Behavior to push events.
    Drupal.behaviors.google_tag_events = {
        attach: function (context, settings) {
            var enabled = drupalSettings.google_tag_events.enabled;

            if (!enabled || enabled.length === 0) {
                return;
            }


            // Add events from inline definition.
          once('processed', "[data-selector='google_tag_events']").forEach(function (el) {
            var $el = $(el),
              events = JSON.parse($el.html());
            $.extend(drupalSettings.google_tag_events.gtmEvents, events);
          });

            // Sort events by weight.
            var weights = drupalSettings.google_tag_events.weights,
                eventsOrder = [];
            for (var index in drupalSettings.google_tag_events.gtmEvents) {
                eventsOrder.push({
                    'weight': weights[index] || 0,
                    'name': index
                });
            }
            eventsOrder.sort(function (a,b) {return a.weight - b.weight});

            // Push events.
            for (var index in eventsOrder) {
                var name = eventsOrder[index].name,
                    event = drupalSettings.google_tag_events.gtmEvents[name];
                window.dataLayer.push(event);
                delete drupalSettings.google_tag_events.gtmEvents[name]
            }

          // When running in bigpipe the event is flushed after headers
          // have been sent so the cookie cannot be removed. This can cause
          // the event to be pushed multiple times. For this case
          // we remove the cookie here.
          if (cookies) {
            cookies.remove('STYXKEY_gte_ptsc_google_tag_events', {path: "/", domain: window.location.host})
          }
        }
    };

}(jQuery, Drupal, once, drupalSettings, window.Cookies));
