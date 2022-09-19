<?php

namespace Drupal\google_news_api\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GoogleNewsPrepareForm extends FormBase {

  private $data;

  /**
   * @var PrivateTempStoreFactory
   */
  private PrivateTempStoreFactory $tempratory;

  public function __construct(PrivateTempStoreFactory $privateTempStoreFactory) {
    $this->tempratory = $privateTempStoreFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
    );
  }

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
      '#default_value' => 'none',
      '#options' => [
        'none' => 'Choose news type',
        'top' => 'Top Headlines',
        'every' => 'Everything'
      ],
      '#ajax' => [
        'callback' => '::ajaxCallback',
        'wrapper' => 'news_form',
      ]
    ];

    $form['news_form'] = [
      '#type' => 'details',
      '#title' => $this->t('Settings'),
      '#open' => TRUE,
      '#attributes' => ['id' => 'news_form'],
    ];


      $form['news_form']['label'] = [
        '#markup' => $this->t('Please choose type of news.'),
      ];


    // Top news.
    if ($form_state->getValue('type') === "top") {

      $form['news_form']['label']['#access'] = FALSE;

      if ($this->getOptions($top_head_config['countries'])) {
        $form['news_form']['countries'] = [
          '#type' => 'checkboxes',
          '#title' => $this->t('Please, choose country'),
          '#options' => $this->getOptions($top_head_config['countries']),
        ];
      }

      if ($this->getOptions($top_head_config['categories'])) {
        $form['news_form']['categories'] = [
          '#type' => 'checkboxes',
          '#title' => $this->t('Please, choose category'),
          '#options' => $this->getOptions($top_head_config['categories']),
        ];
      }

      if ($this->getOptions($top_head_config['sources'])) {
        $form['news_form']['sources'] = [
          '#type' => 'checkboxes',
          '#multiple' => TRUE,
          '#title' => $this->t('Please, choose source'),
          '#options' => $this->getOptions($top_head_config['sources']),
        ];
      }

      $form['news_form']['q_keys'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Please, write key phrase that you need'),
        '#default_value' => $top_head_config['q_keys'],
        '#description' => $this->t('Add phrases across coma (Example: fish and meet, bad guys).'),
      ];

      $form['news_form']['number'] = [
        '#type' => 'number',
        '#title' => $this->t('Max count of news'),
        '#default_value' => 20,
      ];

      $form['news_form']['button'] = [
        '#type' => 'submit',
        '#submit' => ['::getTopNews'],
        '#value' => $this->t('Download'),
        '#attributes' => [
          'class' => ['btn btn-secondary'],
        ],
        '#ajax' => [
          'callback' => '::addTopNewsCallback',
          'wrapper' => 'result',
        ],
      ];



      $form['news_form']['container'] = [
        '#type' => 'container',
        '#attributes' => ['id' => 'result'],
      ];

      if (\Drupal::service('tempstore.private')->get($form_state->getValue('type'))->get('data')) {
        $form['news_form']['container']['table'] = [
          '#type' => 'tableselect',
          '#header' => [
            'id' => $this->t('ID'),
            'url' => $this->t('URL')
          ],
          '#options' => \Drupal::service('tempstore.private')
              ->get($form_state->getValue('type'))
              ->get('data') ?? [],
          '#empty' => $this->t('No shapes found'),

        ];
        $form['news_form']['container']['button2'] = [
          '#type' => 'submit',
          '#submit' => ['::clearTopNews'],
          '#value' => $this->t('Clear'),
          '#attributes' => [
            'class' => ['btn btn-warning'],
          ],
          '#ajax' => [
            'callback' => '::addTopNewsCallback',
            'wrapper' => 'result',
          ],
        ];
      }

    }

    // Everything news.
    if ($form_state->getValue('type') === "every") {

      $form['news_form']['label']['#access'] = FALSE;

      if ($this->getOptions($everything_config['sources'])) {
        $form['news_form']['sources'] = [
          '#type' => 'checkboxes',
          '#multiple' => TRUE,
          '#title' => $this->t('Please, choose source'),
          '#options' => $this->getOptions($everything_config['sources']),
        ];
      }

      if ($this->getOptions($everything_config['searchIn'])) {
        $form['news_form']['searchIn'] = [
          '#type' => 'checkboxes',
          '#multiple' => TRUE,
          '#title' => $this->t('Please, choose case of searching keys phrases'),
          '#options' => $this->getOptions($everything_config['searchIn']),
        ];
      }

      if ($this->getOptions($everything_config['domains'])) {
        $form['news_form']['domains'] = [
          '#type' => 'checkboxes',
          '#multiple' => TRUE,
          '#title' => $this->t('Please, choose case of searching keys phrases'),
          '#options' => $this->getOptions($everything_config['domains']),
        ];
      }

      if ($this->getOptions($everything_config['exclude_domains'])) {
        $form['news_form']['exclude_domains'] = [
          '#type' => 'checkboxes',
          '#multiple' => TRUE,
          '#title' => $this->t('Please, choose exclude domain'),
          '#options' => $this->getOptions($everything_config['exclude_domains']),
        ];
      }

      if ($this->getOptions($everything_config['languages'])) {
        $form['news_form']['languages'] = [
          '#type' => 'checkboxes',
          '#multiple' => TRUE,
          '#title' => $this->t('Please, choose language'),
          '#options' => $this->getOptions($everything_config['languages']),
        ];
      }

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

  function ajaxCallback(array &$form, FormStateInterface $form_state) {
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
    if ($string === "") {
      return [];
    }
    $arr = explode(',', $string);
    $result = [];
    foreach ($arr as $item) {
      $result[trim($item)] = trim($item);
    }
    return $result;
  }

  public function getTopNews(array &$form, FormStateInterface $form_state) {
    $tempstore = \Drupal::service('tempstore.private')->get('top');
    $data = $tempstore->get('data') ?? [];
    $data[] = [
      'id' => 'sdfsdf333',
      'url' => 'sdfdsf'
    ];
    $tempstore->set('data', $data);
    $form_state->setRebuild();
  }

  public function addTopNewsCallback(array &$form, FormStateInterface $form_state) {
    return $form['news_form']['container'];
  }

  public function clearTopNews(array &$form, FormStateInterface $form_state) {
    $tempstore = \Drupal::service('tempstore.private')->get('top');
    $tempstore->set('data', []);
    $form_state->setRebuild();
  }

}
