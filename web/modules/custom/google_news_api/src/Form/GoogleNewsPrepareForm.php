<?php

namespace Drupal\google_news_api\Form;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\google_news_api\GoogleNewsAPI;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GoogleNewsPrepareForm extends FormBase {

  private $data;

  /**
   * @var PrivateTempStoreFactory
   */
  private PrivateTempStoreFactory $tempratory;

  /**
   * @var GoogleNewsAPI
   */
  private GoogleNewsAPI $googleNewsAPI;

  public function __construct(PrivateTempStoreFactory $privateTempStoreFactory, GoogleNewsAPI $googleNewsAPI) {
    $this->tempratory = $privateTempStoreFactory;
    $this->googleNewsAPI = $googleNewsAPI;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      $container->get('google_news_api')
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
      if(empty($config->get('google_news_api_key')) | $config->get('google_news_api_key') == "") {
        $form['news_form']['label'] = [
          '#markup' => $this->t('Please set API key in settings.'),
        ];
      }
      else {

        $form['news_form']['label']['#access'] = FALSE;

        if ($this->getOptions($top_head_config['countries'])) {
          $form['news_form']['countries'] = [
            '#type' => 'radios',
            '#title' => $this->t('Please, choose country'),
            '#options' => $this->getOptions($top_head_config['countries']),
            '#required' => TRUE,
          ];
        }

        if ($this->getOptions($top_head_config['categories'])) {
          $form['news_form']['categories'] = [
            '#type' => 'radios',
            '#title' => $this->t('Please, choose category'),
            '#options' => $this->getOptions($top_head_config['categories']),
            '#required' => TRUE,
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

        if (\Drupal::service('tempstore.private')
          ->get($form_state->getValue('type'))
          ->get('data')) {
          $form['news_form']['container']['table'] = [
            '#type' => 'table',
            '#header' => [
              'id' => $this->t('ID'),
              'source' => $this->t('Source'),
              'title' => $this->t('Title'),
              'url' => $this->t('URL'),
            ],
            '#rows' => \Drupal::service('tempstore.private')
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
    }

    // Everything news.
    if ($form_state->getValue('type') === "every") {
      if(empty($config->get('google_news_api_key')) | $config->get('google_news_api_key') == "") {
        $form['news_form']['label'] = [
          '#markup' => $this->t('Please set API key in settings.'),
        ];
      }
      else {

        $form['news_form']['label']['#access'] = FALSE;

        if ($this->getOptions($everything_config['languages'])) {
          $form['news_form']['languages'] = [
            '#type' => 'radios',
            '#multiple' => TRUE,
            '#title' => $this->t('Please, choose language'),
            '#options' => $this->getOptions($everything_config['languages']),
            '#required' => TRUE,
          ];
        }

        $form['news_form']['q_keys'] = [
          '#type' => 'textarea',
          '#title' => $this->t('Please, write key phrase that you need'),
          '#default_value' => $top_head_config['q_keys'] ?? "",
          '#description' => $this->t('Add phrases across coma (Example: fish and meet, bad guys).'),
          '#ajax' => [
            'callback' => '::addSearchIn',
            'wrapper' => 'search_in',
            'event' => 'change',
          ],
          '#required' => TRUE,
        ];

        $form['news_form']['search_in'] = [
          '#type' => 'container',
          '#attributes' => ['id' => 'search_in'],
        ];

        if ($form_state->getValue('q_keys') != "") {
          $form['news_form']['search_in']['searchIn'] = [
            '#type' => 'checkboxes',
            '#multiple' => TRUE,
            '#title' => $this->t('Please, choose case of searching keys phrases'),
            '#options' => $this->getOptions($everything_config['searchIn']),
            '#default_value' => $this->getOptions($everything_config['searchIn']),
          ];
        }

        if ($this->getOptions($everything_config['sources'])) {
          $form['news_form']['sources'] = [
            '#type' => 'checkboxes',
            '#multiple' => TRUE,
            '#title' => $this->t('Please, choose source'),
            '#options' => $this->getOptions($everything_config['sources']),
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



        $form['news_form']['sort_by'] = [
          '#type' => 'select',
          '#title' => $this->t('Sort getting articles by'),
          '#default_value' => $everything_config['sort_by'] ?? "",
          '#options' => [
            'relevancy' => $this->t('Relevancy'),
            'popularity' => $this->t('Popularity'),
            'publishedAt' => $this->t('Time published'),
          ],
        ];

        $form['news_form']['from'] = [
          '#type' => 'datetime',
          '#title' => $this->t('Set start time for news publishing'),
          '#required' => TRUE,
          '#default_value' => DrupalDateTime::createFromTimestamp(time() - 60 * 60 * 24),
        ];

        $form['news_form']['to'] = [
          '#type' => 'datetime',
          '#title' => $this->t('Set end time for news publishing'),
          '#required' => TRUE,
          '#default_value' => DrupalDateTime::createFromTimestamp(time()),
        ];

        $form['news_form']['page_size'] = [
          '#type' => 'number',
          '#title' => $this->t('Max count of news'),
          '#default_value' => 5,
        ];

        $form['news_form']['button'] = [
          '#type' => 'submit',
          '#submit' => ['::getEverythingNews'],
          '#value' => $this->t('Download'),
          '#attributes' => [
            'class' => ['btn btn-secondary'],
          ],
          '#ajax' => [
            'callback' => '::addEverythingNewsCallback',
            'wrapper' => 'result2',
          ],
        ];

        $form['news_form']['container2'] = [
          '#type' => 'container',
          '#attributes' => ['id' => 'result2'],
        ];

        if (\Drupal::service('tempstore.private')
          ->get($form_state->getValue('type'))
          ->get('data')) {
          $form['news_form']['container2']['table'] = [
            '#type' => 'table',
            '#header' => [
              'id' => $this->t('ID'),
              'source' => $this->t('Source'),
              'title' => $this->t('Title'),
              'url' => $this->t('URL'),
            ],
            '#rows' => \Drupal::service('tempstore.private')
                ->get($form_state->getValue('type'))
                ->get('data') ?? [],
            '#empty' => $this->t('No shapes found'),

          ];
          $form['news_form']['container2']['button2'] = [
            '#type' => 'submit',
            '#submit' => ['::clearEverythingNews'],
            '#value' => $this->t('Clear'),
            '#attributes' => [
              'class' => ['btn btn-warning'],
            ],
            '#ajax' => [
              'callback' => '::addEverythingNewsCallback',
              'wrapper' => 'result2',
            ],
          ];
        }
      }
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

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return void
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getTopNews(array &$form, FormStateInterface $form_state) {
    $tempstore = \Drupal::service('tempstore.private')->get('top');
    $data = $tempstore->get('data') ?? [];
    $response = \Drupal::service('google_news_api')->getTopHeadLines(
      $form_state->getValue('countries'),
      $form_state->getValue('categories'),
      $form_state->getValue('q_keys') != '' ? $form_state->getValue('q_keys') : NULL,
      $form_state->getValue('sources') != '' ? $form_state->getValue('sources') : NULL,
      $form_state->getValue('number')
    );
    $tempstore->set('data', $this->generateDataForTable($data, $response, $form_state->getValue('number'), 'top'));
    $tempstore->set('data2', array_slice($response['articles'], 0, $form_state->getValue('page_size')));
    $form_state->setRebuild();
  }

  public function addTopNewsCallback(array &$form, FormStateInterface $form_state) {
    return $form['news_form']['container'];
  }

  public function clearTopNews(array &$form, FormStateInterface $form_state) {
    $tempstore = \Drupal::service('tempstore.private')->get('top');
    $tempstore->delete('data');
    $tempstore->delete('data2');
    $form_state->setRebuild();
  }

  public function addEverythingNewsCallback(array &$form, FormStateInterface $form_state) {
    return $form['news_form']['container2'];
  }

  public function getEverythingNews(array &$form, FormStateInterface $form_state) {
    $tempstore = \Drupal::service('tempstore.private')->get('every');
    $data = $tempstore->get('data') ?? [];
    $response = \Drupal::service('google_news_api')->getEverything(
      $form_state->getValue('q_keys') != '' ? $form_state->getValue('q_keys') : NULL,
      $form_state->getValue('searchIn') != NULL ? implode(',',$form_state->getValue('searchIn')) : NULL,
      $form_state->getValue('sources') != NULL ? implode(',',$form_state->getValue('sources')) : NULL,
      $form_state->getValue('domains') != NULL ? implode(',',$form_state->getValue('domains')) : NULL,
      $form_state->getValue('exclude_domains') != NULL ? implode(',',$form_state->getValue('exclude_domains')) : NULL,
      $form_state->getValue('from'),
      $form_state->getValue('to'),
      $form_state->getValue('languages'),
      $form_state->getValue('sort_by'),
      $form_state->getValue('page_size'),
    );
    $tempstore->set('data', $this->generateDataForTable($data, $response, $form_state->getValue('page_size'), 'every'));
    $tempstore->set('data2', array_slice($response['articles'], 0, $form_state->getValue('page_size')));
    $form_state->setRebuild();

  }

  public function clearEverythingNews(array &$form, FormStateInterface $form_state) {
    $tempstore = \Drupal::service('tempstore.private')->get('every');
    $tempstore->delete('data');
    $tempstore->delete('data2');
    $form_state->setRebuild();
  }

  private function generateDataForTable(array &$data, array $request, $length, $type): array {
    $articles = array_slice($request['articles'], 0, $length);
    $i = 0;
    foreach ($articles as $article) {
      $data[] = [
        'id' => $i,
        'source' => $article['source']['name'],
        'title' => Markup::create('<a href="' . $article['url'] . '" target="_blank">' . $article['title']  . '</a>'),
        'url' => Markup::create('<a href="/news/create/' . $type . '/' . $i .' " target="_blank">' . $this->t('Create article') . '</a>')
      ];
      $i++;
    }
    return $data;
  }

  public function addSearchIn(array &$form, FormStateInterface $form_state) {
    return $form['news_form']['search_in'];
  }


}
