<?php
namespace Drupal\open_weather\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class OpenWeatherSettingsForm extends ConfigFormBase {

  /**
   * {@inheritDoc}
   */
  protected function getEditableConfigNames(): array {
    return ['open_weather.settings'];
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId(): string {
    return 'open_weather_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('open_weather.settings');
    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Open Weather API key'),
      '#description' => $this->t('<a href="@url" target="_blank">You can register your API key here</a>.', ['@url' => 'https://openweathermap.org']),
      '#default_value' => $config->get('api_key'),
      '#size' => 30,
      '#maxlength' => 60,
      '#required' => TRUE,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('open_weather.settings');
    $config->set('api_key', $form_state->getValue('api_key'));
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
