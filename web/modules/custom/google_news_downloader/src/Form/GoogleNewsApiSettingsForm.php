<?php
namespace Drupal\google_news_downloader\Form;

use DOMDocument;
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

    $form['top_head_settings']['top_head_settings_frequency'] = [
      '#type' => 'select',
      '#title' => $this->t('Frequency of news updating'),
      '#default_value' => $top_head_config['frequency'],
      '#options' => [
        15 => $this->t('Every @time minutes', ['@time' => 15 ]),
        30 => $this->t('Every @time minutes', ['@time' => 30 ]),
        60 => $this->t('Every @time hour', ['@time' => 1 ]),
        90 => $this->t('Every @time hour', ['@time' => 1.5 ]),
        120 => $this->t('Every @time hour', ['@time' => 2 ]),
      ],
    ];

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

    $form['top_head_settings']['top_head_settings_q_keys'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Key phrases found in the articles'),
      '#default_value' => $top_head_config['q_keys'],
      '#description' => $this->t('Add phrases across coma (Example: fish and meet, bad guys).'),
    ];

    $form['top_head_settings']['top_head_settings_sources'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Sources names of articles'),
      '#default_value' => $top_head_config['sources'],
      '#description' => $this->t('Add <a href="https://newsapi.org/sources" target="_blank">sources id</a> across coma. Note: you can not mix this param with the country or category params.'),
    ];

    $form['top_head_settings']['details'] = array(
      '#type' => 'details',
      '#title' => $this->t('Additional'),
      '#open' => FALSE, // Controls the HTML5 'open' attribute. Defaults to FALSE.
    );

    $form['top_head_settings']['details']['top_head_settings_page_size'] = [
      '#type' => 'number',
      '#title' => $this->t('Max count of articles in one request.'),
      '#default_value' => $top_head_config['page_size'],
    ];

    $form['top_head_settings']['details']['top_head_settings_page'] = [
      '#type' => 'number',
      '#title' => $this->t('Count of pages with articles on one page in one request.'),
      '#default_value' => $top_head_config['page'],
    ];

    // Settings for "Everything news".
    $everything_config = $config->get('everything_settings');
    $form['everything_settings'] = [
      '#type' => 'details',
      '#title' => t('Settings for "Everything news"'),
      '#description' => $this->t('Here you can configure news downloader for "Everything news"'),
      '#open' => TRUE,
    ];

    $form['everything_settings']['everything_settings_frequency'] = [
      '#type' => 'select',
      '#title' => $this->t('Frequency of "Everything news" downloading'),
      '#default_value' => $everything_config['frequency'],
      '#options' => [
        60 => $this->t('Every @time hour', ['@time' => 1 ]),
        180 => $this->t('Every @time hour', ['@time' => 3 ]),
        360 => $this->t('Every @time hour', ['@time' => 6 ]),
        720 => $this->t('Every @time hour', ['@time' => 12 ]),
        1440 => $this->t('Every @time hour', ['@time' => 24 ]),
      ],
    ];

    $form['everything_settings']['everything_settings_q_keys'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Key phrases found in the articles'),
      '#default_value' => $everything_config['q_keys'],
      '#description' => $this->t('Add phrases across coma (Example: fish and meet, bad guys).'),
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

    $form['everything_settings']['everything_settings_sort_by'] = [
      '#type' => 'select',
      '#title' => $this->t('Sort getting articles by'),
      '#default_value' => $everything_config['sort_by'],
      '#options' => [
        'relevancy' => $this->t('Relevancy'),
        'popularity' => $this->t('Popularity'),
        'publishedAt' => $this->t('Time published'),
      ],
    ];

    $form['everything_settings']['details'] = array(
      '#type' => 'details',
      '#title' => $this->t('Additional'),
      '#open' => FALSE,
    );

    $form['everything_settings']['details']['everything_settings_page_size'] = [
      '#type' => 'number',
      '#title' => $this->t('Max count of news in one request'),
      '#default_value' => $everything_config['page_size'],
    ];

    $form['everything_settings']['details']['everything_settings_page'] = [
      '#type' => 'number',
      '#title' => $this->t('Count of pages with news on one page in one request'),
      '#default_value' => $everything_config['page'],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get general configs.
    $config = $this->config('google_news_downloader.settings');

    // Setting Google API key config.
    $config->set('google_news_api_key', $form_state->getValue('google_news_api_key'));

    // Setting configs for "Top Headlines news".
    $top_head_config = [
      'frequency'=> $form_state->getValue('top_head_settings_frequency'),
      'countries'=> $form_state->getValue('top_head_settings_countries'),
      'categories'=> $form_state->getValue('top_head_settings_categories'),
      'q_keys'=> $form_state->getValue('top_head_settings_q_keys'),
      'sources'=> $form_state->getValue('top_head_settings_sources'),
      'page_size'=> $form_state->getValue('top_head_settings_page_size'),
      'page'=> $form_state->getValue('top_head_settings_page'),
    ];
    $config->set('top_head_settings', $top_head_config);

    // Setting configs for "Everything news".
    $everything_config = [
      'frequency'=> $form_state->getValue('everything_settings_frequency'),
      'q_keys'=> $form_state->getValue('everything_settings_q_keys'),
      'searchIn'=> $form_state->getValue('everything_settings_searchIn'),
      'sources'=> $form_state->getValue('everything_settings_sources'),
      'domains'=> $form_state->getValue('everything_settings_domains'),
      'exclude_domains'=> $form_state->getValue('everything_settings_exclude_domains'),
      'languages'=> $form_state->getValue('everything_settings_languages'),
      'sort_by'=> $form_state->getValue('everything_settings_sort_by'),
      'page_size'=> $form_state->getValue('everything_settings_page_size'),
      'page'=> $form_state->getValue('everything_settings_page'),
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
    if ($form_state->getValue('top_head_settings_q_keys') !== "" & preg_match('/^[A-Za-zа-щА-ЩЬьЮюЯяЇїІіЄєҐґ\-" ]+(?:, [A-Za-zа-щА-ЩЬьЮюЯяЇїІіЄєҐґ\-" ]+)*$/u', $form_state->getValue('top_head_settings_q_keys')) != 1) {
      $form_state->setError($form['top_head_settings']['top_head_settings_q_keys'], $this->t('Please, add phrases across coma and space, without numbers (Example: fish and meet, bad guys).'));
    }
    if (($form_state->getValue('top_head_settings_countries') != "" & $form_state->getValue('top_head_settings_sources') !== "" ) ||
        ($form_state->getValue('top_head_settings_categories') != ""  & $form_state->getValue('top_head_settings_sources') !== "" ) ||
        ($form_state->getValue('top_head_settings_sources') !== "" & preg_match('/^[A-Za-z0-9 ]+(?:, [A-Za-z0-9 ]+)*$/', $form_state->getValue('top_head_settings_sources')) != 1)) {
      $form_state->setError($form['top_head_settings']['top_head_settings_sources'], $this->t("Please, add sources across coma (Example: BBC, The Washington Post). Note: you can't mix this param with the country or category params."));
    }
    if ($form_state->getValue('everything_settings_q_keys') !== "" & preg_match('/^[A-Za-zа-щА-ЩЬьЮюЯяЇїІіЄєҐґ\-" ]+(?:, [A-Za-zа-щА-ЩЬьЮюЯяЇїІіЄєҐґ\-" ]+)*$/u', $form_state->getValue('everything_settings_q_keys')) != 1) {
      $form_state->setError($form['everything_settings']['everything_settings_q_keys'], $this->t('Please, add phrases across coma (Example: fish and meet, bad guys).'));
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


}
