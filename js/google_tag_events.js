/**
 * @file
 * Provide behavior of GTM tracking and tagging.
 */

window.dataLayer = window.dataLayer || [];

drupalSettings.google_tag_events = drupalSettings.google_tag_events || {};
drupalSettings.google_tag_events.gtmEvents = drupalSettings.google_tag_events.gtmEvents || {};
drupalSettings.google_tag_events.enabled = drupalSettings.google_tag_events.enabled || false;

(function ($, once) {
    'use strict';

    // Behavior to push events.
    Drupal.behaviors.google_tag_events = {
        attach: function (context, settings) {
            var enabled = drupalSettings.google_tag_events.enabled;

            if (!enabled || enabled.length === 0) {
                return;
            }


            // Add events from inline definition.
            once('processed', "[data-selector='google_tag_events']").forEach(function (elem) {
                var events = JSON.parse($(elem).html());
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
        }
    };

}(jQuery, once));
