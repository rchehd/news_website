<?php
namespace Drupal\google_news_api\Form;

use DOMDocument;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Site\Settings;
use Drupal\field\Entity\FieldConfig;

class GoogleNewsApiSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['google_news_api.settings'];
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
    $config = $this->config('google_news_api.settings');
    $entityTypeManager = \Drupal::service('entity_type.manager');

    $types = [];
    $contentTypes = $entityTypeManager->getStorage('node_type')->loadMultiple();
    foreach ($contentTypes as $contentType) {
      $types[$contentType->id()] = $contentType->label();
    }

    $form['google_news_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Google News API key'),
      '#description' => $this->t('<a href="@url" target="_blank">You can register your API key here</a>.', ['@url' => 'https://newsapi.org/register']),
      '#default_value' => $config->get('google_news_api_key'),
      '#size' => 30,
      '#maxlength' => 60,
      '#required' => TRUE,
    ];

    // Settings for "Top headline news".
    $top_head_config = $config->get('top_head_settings');
    $form['top_head_settings'] = array(
      '#type' => 'details',
      '#title' => t('Settings for "Top headline news"'),
      '#description' => $this->t('Here you can configure news downloader for "Top headline news"'),
      '#open' => TRUE,
    );

    $form['top_head_settings']['content_type_top'] = [
      '#type' => 'select',
      '#title' => $this->t('Choose content type to use for Top Headline news'),
      '#default_value' => $top_head_config['content_type'] ?? reset($types),
      '#options' => $types,
      '#required' => TRUE,
      '#description' => $this->t('<a href="/admin/structure/types/add" >You can create content type here.</a>'),
      '#ajax' => [
        'callback' => '::showContentTypesFields',
        'wrapper' => 'content_type_fields',
      ],
    ];

    $form['top_head_settings']['container'] = [
      '#type' => 'details',
      '#attributes' => ['id' => 'content_type_fields'],
      '#open' => TRUE,
      '#title' => t('Set field value'),
    ];

    $form['top_head_settings']['container']['container2'] = [
      '#type' => 'container',
    ];

    $form['top_head_settings']['container']['container2']['content_type_fields_top'] = $this->getComparison($form_state->getValue('content_type_top') ?? $top_head_config['content_type']);

    $form['top_head_settings']['top_head_settings_countries'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Available countries'),
      '#default_value' => $top_head_config['countries'],
      '#description' => $this->t('Add the 2-letter ISO 3166-1 code of the country across coma (Example: ua, us).'),
    ];

    $form['top_head_settings']['top_head_settings_categories'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Categories'),
      '#default_value' => $top_head_config['categories'],
      '#description' => $this->t('Add category across coma (Example: sport, politic).'),
    ];

    $form['top_head_settings']['top_head_settings_sources'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Sources names of articles'),
      '#default_value' => $top_head_config['sources'],
      '#description' => $this->t('Add <a href="/admin/structure/types/add" >sources id</a> across coma. Note: you can not mix this param with the country or category params.'),
    ];

    // Settings for "Everything news".
    $everything_config = $config->get('everything_settings');
    $form['everything_settings'] = [
      '#type' => 'details',
      '#title' => t('Settings for "Everything news"'),
      '#description' => $this->t('Here you can configure news downloader for "Everything news"'),
      '#open' => TRUE,
    ];

    $form['everything_settings']['content_type_every'] = [
      '#type' => 'select',
      '#title' => $this->t('Choose content type to use for Everything news'),
      '#default_value' => $everything_config['content_type'] ?? reset($types),
      '#options' => $types,
      '#required' => TRUE,
      '#description' => $this->t('<a href="/admin/structure/types/add" >You can create content type here.</a>'),
    ];

    $form['everything_settings']['everything_settings_sources'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Sources names of articles'),
      '#default_value' => $everything_config['sources'],
      '#description' => $this->t('Add <a href="https://newsapi.org/sources" target="_blank">sources id</a> across coma. Note: you can not mix this param with the country or category params.'),
    ];

    $form['everything_settings']['everything_settings_searchIn'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Search in (the field to restrict your key phrase search to)'),
      '#default_value' => $everything_config['searchIn'],
      '#description' => $this->t('Add fields across coma (Example: title, content, description).'),
    ];

    $form['everything_settings']['everything_settings_domains'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Domains for getting articles'),
      '#default_value' => $everything_config['domains'],
      '#description' => $this->t('Domains to restrict the search to. (Example: bbc.co.uk, techcrunch.com, engadget.com). If value empty it will search from all domains.'),
    ];

    $form['everything_settings']['everything_settings_exclude_domains'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Excluded domains for getting articles'),
      '#default_value' => $everything_config['exclude_domains'],
      '#description' => $this->t('Exclude domains to restrict the search to. (Example: bbc.co.uk, techcrunch.com, engadget.com).'),
    ];

    $form['everything_settings']['everything_settings_languages'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Languages for getting articles'),
      '#default_value' => $everything_config['languages'],
      '#description' => $this->t('The 2-letter ISO-639-1 code of the language you want to get headlines for across coma. (Example: en, es, ua).'),
    ];


    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get general configs.
    $config = $this->config('google_news_api.settings');

    // Setting Google API key config.
    $config->set('google_news_api_key', $form_state->getValue('google_news_api_key'));

    // Setting configs for "Top Headlines news".
    $top_head_config = [
      'countries'=> $form_state->getValue('top_head_settings_countries'),
      'categories'=> $form_state->getValue('top_head_settings_categories'),
      'sources'=> $form_state->getValue('top_head_settings_sources'),
      'content_type'=> $form_state->getValue('content_type_top'),
    ];
    $config->set('top_head_settings', $top_head_config);

    // Setting configs for "Everything news".
    $everything_config = [
      'searchIn'=> $form_state->getValue('everything_settings_searchIn'),
      'sources'=> $form_state->getValue('everything_settings_sources'),
      'domains'=> $form_state->getValue('everything_settings_domains'),
      'exclude_domains'=> $form_state->getValue('everything_settings_exclude_domains'),
      'languages'=> $form_state->getValue('everything_settings_languages'),
      'content_type'=> $form_state->getValue('content_type_every'),
    ];
    $config->set('everything_settings', $everything_config);

    $config->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('top_head_settings_countries') !== "" & preg_match('/^[a-z]{2}(?:, [a-z]{2})*$/', $form_state->getValue('top_head_settings_countries')) != 1) {
      $form_state->setError($form['top_head_settings']['top_head_settings_countries'], $this->t('Please, add the 2-letter ISO 3166-1 code of the country across coma and space (Example: ua, us)'));
    }
    if ($form_state->getValue('top_head_settings_categories') !== "" & preg_match('/^\w+(?:, \w+)*$/', $form_state->getValue('top_head_settings_categories')) != 1) {
      $form_state->setError($form['top_head_settings']['top_head_settings_categories'], $this->t('Please, add category across coma (Example: sport, politic).'));
    }
    if ($form_state->getValue('top_head_settings_sources') !== "" & preg_match('/^[A-Za-z0-9 ]+(?:, [A-Za-z0-9 ]+)*$/', $form_state->getValue('top_head_settings_sources')) != 1) {
      $form_state->setError($form['top_head_settings']['top_head_settings_sources'], $this->t("Please, add sources across coma (Example: BBC, The Washington Post). Note: you can't mix this param with the country or category params."));
    }
    if ($form_state->getValue('everything_settings_searchIn') !== "" & preg_match('/^[A-Za-z ]+(?:, [A-Za-z ]+)*$/', $form_state->getValue('everything_settings_searchIn')) != 1) {
      $form_state->setError($form['everything_settings']['everything_settings_searchIn'], $this->t('Please, add fields across coma (Example: title, content, description)'));
    }
    if ($form_state->getValue('everything_settings_sources') !== "" & preg_match('/^[A-Za-z0-9 ]+(?:, [A-Za-z0-9 ]+)*$/', $form_state->getValue('everything_settings_sources')) != 1) {
      $form_state->setError($form['everything_settings']['everything_settings_sources'], $this->t("Please, add sources across coma (Example: BBC, The Washington Post). Note: you can't mix this param with the country or category params."));
    }
    if ($form_state->getValue('everything_settings_domains') !== "" & preg_match('/^[A-Za-z0-9. ]+(?:, [A-Za-z0-9. ]+)*$/', $form_state->getValue('everything_settings_domains')) != 1) {
      $form_state->setError($form['everything_settings']['everything_settings_domains'], $this->t('Please, add domain across coma (Example: bbc.co.uk, techcrunch.com, engadget.com)'));
    }
    if ($form_state->getValue('everything_settings_exclude_domains') !== "" & preg_match('/^[A-Za-z0-9. ]+(?:, [A-Za-z0-9. ]+)*$/', $form_state->getValue('everything_settings_exclude_domains')) != 1) {
      $form_state->setError($form['everything_settings']['everything_settings_exclude_domains'], $this->t('Please, add domain across coma (Example: bbc.co.uk, techcrunch.com, engadget.com)'));
    }
    if ($form_state->getValue('everything_settings_languages') !== "" & preg_match('/^[a-z]{2}(?:, [a-z]{2})*$/', $form_state->getValue('everything_settings_languages')) != 1) {
      $form_state->setError($form['everything_settings']['everything_settings_languages'],$this->t('Please, add the 2-letter ISO-639-1 code of the language you want to get headlines for across coma. (Example: en, es, ua).'));
    }
  }

  public function showContentTypesFields(array &$form, FormStateInterface $form_state) {
//    $all_fields = \Drupal::service('entity_field.manager')->getFieldDefinitions('node', $form_state->getValue('content_type_top'));
//    $fields = [];
//    $options = Settings::get('top_headline_news_keys');
//    foreach ($all_fields as $field) {
//      if ($field instanceof FieldConfig) {
//        $fields[] = $field;
//      }
//    }
//    foreach ($fields as $field) {
//      if ($field instanceof FieldConfig) {
//        $form['top_head_settings']['container'][$field->get('field_name') . '_top'] = [
//          '#type' => 'select',
//          '#title' => $field->label(),
//          '#default_value' => null,
//          '#options' => $options,
//          '#empty_option' => $this->t('- None -'),
//        ];
//      }
//
//    }
    return $form['top_head_settings']['container'];
  }

  private function getComparison(mixed $content_type): array {
    if ($content_type == null) {
      $content_type = 'article';
    }
    $all_fields = \Drupal::service('entity_field.manager')->getFieldDefinitions('node', $content_type);
    $fields = [];
    $result = [];
    $options = Settings::get('top_headline_news_keys');
    foreach ($all_fields as $field) {
      if ($field instanceof FieldConfig) {
        $fields[] = $field;
      }
    }
    foreach ($fields as $field) {
      if ($field instanceof FieldConfig) {
        $result[$field->get('field_name') . '_top'] = [
          '#type' => 'select',
          '#title' => $field->label(),
          '#default_value' => null,
          '#options' => $options,
          '#empty_option' => $this->t('- None -'),
        ];
      }
    }
    return $result;

  }

}
