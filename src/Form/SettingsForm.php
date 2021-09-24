<?php

namespace Drupal\google_tag_events\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the Google tag manager events module settings form.
 */
class SettingsForm extends ConfigFormBase {

  const CONFIG_NAME = 'google_tag_events.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'google_tag_events_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [self::CONFIG_NAME];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(self::CONFIG_NAME);

    $form['debug_mode'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Debug mode'),
      '#default_value' => $config->get('debug_mode'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config(self::CONFIG_NAME);
    $config->set('debug_mode', $form_state->getValue('debug_mode'));
    $config->save();

    parent::submitForm($form, $form_state);
  }

}
