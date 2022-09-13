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
    $config = $this->config('google_news_downloader.settings');
    $form['text'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Google News API settings'),
    ];

    $google_news_api_key = $config->get('google_news_api_key');
    $form['google_news_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API key'),
      '#description' => $this->t('Enter your private API key'),
      '#default_value' => $this->t($google_news_api_key),
      '#size' => 30,
      '#maxlength' => 60,
      '#required' => TRUE,
    ];

    // Settings for "Top headline news".
    $form['top_head_settings'] = array(
      '#type' => 'details',
      '#title' => t('Settings for "Top headline news"'),
      '#description' => $this->t('Here you can configure news downloader for "Top headline news"'),
      '#open' => TRUE, // Controls the HTML5 'open' attribute. Defaults to FALSE.
    );

    $form['top_head_settings']['top_head_line_frequency'] = [
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

    $form['top_head_settings']['countries'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Counties of news'),
      '#default_value' => $config->get('countries'),
      '#description' => $this->t('Add country across coma (Example: ua, us).'),
    ];

    $form['top_head_settings']['category'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Categories of news'),
      '#default_value' => $config->get('categories'),
      '#description' => $this->t('Add category across coma (Example: sport, politic).'),
    ];

    $form['top_head_settings']['q_keys'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Key phrases found in the news'),
      '#default_value' => $config->get('q_keys'),
      '#description' => $this->t('Add phrases across coma (Example: fish and meet, bad guys).'),
    ];

    $form['top_head_settings']['sources'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Sources of news'),
      '#default_value' => $config->get('sources'),
      '#description' => $this->t('Add sources across coma (Example: BBC, The Washington Post).'),
    ];

    $form['top_head_settings']['details'] = array(
      '#type' => 'details',
      '#title' => $this->t('Additional'),
      '#open' => FALSE, // Controls the HTML5 'open' attribute. Defaults to FALSE.
    );

    $form['top_head_settings']['details']['page_size'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Max count of news in one request'),
      '#default_value' => $config->get('page_size'),
    ];

    $form['top_head_settings']['details']['page'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Count of pages with news on one page in one request'),
      '#default_value' => $config->get('page'),
    ];

    // Settings for "Everything news".
    $form['top_head_settings'] = [
      '#type' => 'details',
      '#title' => t('Settings for "Everything news"'),
      '#description' => $this->t('Here you can configure news downloader for "Top headline news"'),
      '#open' => TRUE, // Controls the HTML5 'open' attribute. Defaults to FALSE.
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
