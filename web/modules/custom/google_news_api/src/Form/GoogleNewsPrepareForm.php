<?php

namespace Drupal\google_news_api\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class GoogleNewsPrepareForm extends FormBase {

  public function getFormId() {
    return 'google_api_prepare_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('google_news_api.settings');
    $top_head_config = $config->get('top_head_settings');
    $everything_config = $config->get('everything_settings');

    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Please, choose type of news'),
      '#options' => [
        'top' => 'Top Headlines',
        'every' => 'Everything'
      ],
      '#ajax' => [
        'callback' => 'get_news_form',
        'wrapper' => 'news_form',
      ]
    ];

    $form['news_form'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'news_form'
      ],
    ];

    // Top news.
    if ($form_state->getValue('type') === "top") {
      $form['news_form']['countries'] = [
        '#type' => 'select',
        '#title' => $this->t('Please, choose country'),
        '#options' => $this->getOptions($top_head_config['countries']),
      ];

      $form['news_form']['categories'] = [
        '#type' => 'select',
        '#title' => $this->t('Please, choose category'),
        '#options' => $this->getOptions($top_head_config['categories']),
      ];

      $form['news_form']['sources'] = [
        '#type' => 'select',
        '#multiple' => TRUE,
        '#title' => $this->t('Please, choose source'),
        '#options' => $this->getOptions($top_head_config['sources']),
      ];

      $form['news_form']['q_keys'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Please, write key phrase that you need'),
        '#default_value' => $top_head_config['q_keys'],
        '#description' => $this->t('Add phrases across coma (Example: fish and meet, bad guys).'),
      ];
    }

    // Everything news.
    if ($form_state->getValue('type') === "every") {
      $form['news_form']['sources'] = [
        '#type' => 'select',
        '#multiple' => TRUE,
        '#title' => $this->t('Please, choose source'),
        '#options' => $this->getOptions($everything_config['sources']),
      ];

      $form['news_form']['searchIn'] = [
        '#type' => 'select',
        '#multiple' => TRUE,
        '#title' => $this->t('Please, choose case of searching keys phrases'),
        '#options' => $this->getOptions($everything_config['searchIn']),
      ];

      $form['news_form']['domains'] = [
        '#type' => 'select',
        '#multiple' => TRUE,
        '#title' => $this->t('Please, choose case of searching keys phrases'),
        '#options' => $this->getOptions($everything_config['domains']),
      ];

      $form['news_form']['exclude_domains'] = [
        '#type' => 'select',
        '#multiple' => TRUE,
        '#title' => $this->t('Please, choose exclude domain'),
        '#options' => $this->getOptions($everything_config['exclude_domains']),
      ];

      $form['news_form']['languages'] = [
        '#type' => 'select',
        '#multiple' => TRUE,
        '#title' => $this->t('Please, choose language'),
        '#options' => $this->getOptions($everything_config['languages']),
      ];

      $form['news_form']['q_keys'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Please, write key phrase that you need'),
        '#default_value' => $top_head_config['q_keys'],
        '#description' => $this->t('Add phrases across coma (Example: fish and meet, bad guys).'),
      ];

      $form['news_form']['sort_by'] = [
        '#type' => 'select',
        '#title' => $this->t('Sort getting articles by'),
        '#default_value' => $everything_config['sort_by'],
        '#options' => [
          'relevancy' => $this->t('Relevancy'),
          'popularity' => $this->t('Popularity'),
          'publishedAt' => $this->t('Time published'),
        ],
      ];

      $form['news_form']['from'] = [
        '#type' => 'datetime',
        '#title' => $this->t('Set start time for news publishing'),
      ];

      $form['news_form']['to'] = [
        '#type' => 'datetime',
        '#title' => $this->t('Set end time for news publishing'),
      ];

    }


    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement submitForm() method.
  }

  function top_news_form(array &$form, FormStateInterface $form_state) {
    return $form['news_form'];
  }

  /**
   * Get array from string.
   *
   * @param $string
   *
   * @return array
   */
  private function getOptions($string): array {
    $arr = explode(',', $string);
    $result = [];
    foreach ($arr as $item) {
      $result[trim($item)] = trim($item);
    }
    return $result;
  }

}
