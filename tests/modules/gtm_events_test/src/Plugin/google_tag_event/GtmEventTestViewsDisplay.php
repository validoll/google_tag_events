<?php

namespace Drupal\gtm_events_test\Plugin\google_tag_event;

use Drupal\google_tag_events\GoogleTagEventsPluginBase;
use Drupal\views\ViewExecutable;

/**
 * Views display GTM event plugin.
 *
 * @Plugin(
 *   id = "gtm_events_test_views_display",
 *   label = @Translation("Views display GTM event"),
 *   weight = -2
 * )
 */
class GtmEventTestViewsDisplay extends GoogleTagEventsPluginBase {

  /**
   * The view object.
   */
  protected ViewExecutable $view;

  /**
   * Constructs a ProductPrintGtmEventsPlugin object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->view = $configuration['data']['view'] ?? NULL;
    unset($configuration['data']['view']);
  }

  /**
   * {@inheritdoc}
   */
  public function process(array $data = NULL) {
    unset($data['view']);

    $data += [
      'event' => 'views display',
      'data' => $this->getData(),
    ];

    return $data;
  }

  /**
   * Get data from view for event.
   *
   * @return array
   *   Data array.
   */
  protected function getData() {
    $data = [];
    $rows = $this->view->result;

    foreach ($rows as $row) {
      $data = $row->title;
    }

    return $data;
  }

}
