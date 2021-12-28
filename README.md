# Google Tag Manager: Events

## INTRODUCTION
The *Google Tag Manager: Events* module provides API to push events
to GTM Datalayer from PHP.

* For a full description of the module, visit the project page:
  https://www.drupal.org/project/google_tag_events

* To submit bug reports and feature suggestions, or to track changes:
  https://www.drupal.org/project/issues/google_tag_events

## REQUIREMENTS
This module requires [GoogleTagManager](https://www.drupal.org/project/google_tag) module.

## INSTALLATION
Install as you would normally install a contributed Drupal module. Visit:
https://www.drupal.org/docs/extending-drupal/installing-modules
for further information.

## CONFIGURATION
To push the events via *Google Tag Manager: Events* module you must
configure at least one GTM container.

## FOR DEVELOPERS
You can see *gtm_events_test* module for usage examples.

### The goal
You can push an event directly from PHP code. It means that event will
be pushed after page loading in browser.

### How to push event
To push event you can use 'google_tag_events' service.

```php
google_tag_events_service()->setEvent(
  'some_event_name',
  [
    'event' => 'some_event_name'
    'foo' => 'bar'
  ]
);
```

After this code executing will be pushed event like

```js
dataLayer.push({
  'event': 'some_event_name',
  'foo': 'bar'
});
```

### Debug mode
You can test GTM events pushing without any configured GTM containers.
Just enable debug mode on config page */admin/config/system/google-tag/events/settings*.

### How to check
You can use recommended browser extensions to check datalayer:
* [Datalayer Checker](https://chrome.google.com/webstore/detail/datalayer-checker/ffljdddodmkedhkcjhpmdajhjdbkogke) by https://sublimetrix.com
* [dataslayer](https://chrome.google.com/webstore/detail/dataslayer/ikbablmmjldhamhcdjjigniffkkjgpo) by https://dataslayer.org

### Event plugin
You can incapsulate the event data preparation code to event plugin.
Plugin must be placed into
`tests/modules/gtm_events_test/src/Plugin/google_tag_event` directory.

For example:

```php
/**
 * Node node page visit GTM event plugin.
 *
 * @Plugin(
 *   id = "example_event_node_view",
 *   label = @Translation("Node page visit event")
 * )
 */
class ExampleEventNodeView extends GoogleTagEventsPluginBase {

  /**
   * {@inheritdoc}
   */
  public function process(array $data = NULL) {
    $data = $data ?? $this->data;

    $event_data = [
      'event' => 'node_view',
      'title' => $data['node']->getTitle(),
    ];

    return $event_data;
  }

}
```

Then call the service

```php
/**
 * Implements hook_entity_view().
 */
function comment_entity_view(
    array &$build,
    EntityInterface $entity,
    EntityViewDisplayInterface $display,
    $view_mode
  ) {
  if ($entity instanceof NodeInterface) {
    google_tag_events_service()->setEvent(
      'example_event_node_view',
      ['node' => $entity]
    );
  }
}
```

The result of this code will be

```js
dataLayer.push({
  'event': 'node_view',
  'title': 'Some node title'
});
```

A plugin name must match with event name to call plugin's process method.

## MAINTAINERS
* Vyacheslav Malchik (validoll) - https://www.drupal.org/u/validoll
