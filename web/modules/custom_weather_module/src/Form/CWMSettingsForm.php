<?php

namespace Drupal\custom_weather_module\Form;

use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that gives a site needed data to get the weather info.
 */
class CWMSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'custom_weather_module_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'custom_weather_module.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    // $types = node_type_get_names();
    $config = $this->config('custom_weather_module.settings');
    $form['admin_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Admin's API key for the weatherapi.com"),
      '#default_value' => $config->get('api_key'),
      '#description' => $this->t('Input your API key to make the block work'),
    ];
    $form['admin_default_location'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Input default location for the weatherapi.com'),
      '#default_value' => $config->get('location'),
      '#description' => $this->t('Input default location to make the block work'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('custom_weather_module.settings')
      ->set('api_key', $values['admin_api_key'])
      ->set('location', $values['admin_default_location'])
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('custom_weather_module.settings')
      ->set('api_key', $values['admin_api_key'])
      ->save();
    if (!preg_match("/^[a-z0-9 ,]{1,32}$/", $form_state->getValue('admin_api_key'))) {
      $form_state->setErrorByName('api_key', $this->t("The API key isn't correct. Please enter a valid one."));
    }
  }

}
