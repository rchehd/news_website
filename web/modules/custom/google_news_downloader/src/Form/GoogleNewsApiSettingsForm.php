<?php
namespace Drupal\google_news_downloader\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class GoogleNewsApiSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['google_news_downloader.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'gnd_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['text'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Google News API settings'),
    ];

    $google_news_api_key = $this->config('google_news_downloader.settings')->get('google_news_api_key');
    $form['google_news_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API key'),
      '#description' => $this->t('Enter your private API key'),
      '#default_value' => $this->t($google_news_api_key),
      '#size' => 30,
      '#maxlength' => 60,
      '#required' => TRUE,
    ];

    $form['frequency']['top_head_line_frequency'] = [
      '#type' => 'select',
      '#title' => $this->t('Select the frequency of top head line news downloading'),
      '#default_value' => $this->config('google_news_downloader.settings')->get('top_head_line_frequency'),
      '#options' => [
        15 => $this->t('Every @time minutes', ['@time' => 15 ]),
        30 => $this->t('Every @time minutes', ['@time' => 30 ]),
        60 => $this->t('Every @time hour', ['@time' => 1 ]),
        90 => $this->t('Every @time hour', ['@time' => 1.5 ]),
        120 => $this->t('Every @time hour', ['@time' => 2 ]),
      ],
    ];

    $form['frequency']['everything_frequency'] = [
      '#type' => 'select',
      '#title' => $this->t('Select the frequency of everything news downloading'),
      '#default_value' => $this->config('google_news_downloader.settings')->get('everything_frequency'),
      '#options' => [
        60 => $this->t('Every @time hour', ['@time' => 1 ]),
        180 => $this->t('Every @time hour', ['@time' => 3 ]),
        360 => $this->t('Every @time hour', ['@time' => 6 ]),
        720 => $this->t('Every @time hour', ['@time' => 12 ]),
        1440 => $this->t('Every @time hour', ['@time' => 24 ]),
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('google_news_downloader.settings')
      ->set('google_news_api_key', $form_state->getValue('google_news_api_key'))
      ->set('top_head_line_frequency', $form_state->getValue('top_head_line_frequency'))
      ->set('everything_frequency', $form_state->getValue('everything_frequency'))
    ;
    $config->save();
    // Clear cache.
    drupal_flush_all_caches();
    parent::submitForm($form, $form_state);
  }

}
