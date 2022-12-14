<?php

namespace Drupal\google_news_downloader\Plugin\QueueWorker;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\google_news_downloader\GoogleNewsAPI2;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Google top headlines news download worker.
 *
 * @QueueWorker(
 *   id = "google_top_headlines_downloader",
 *   title = @Translation("Google top headlines news downloader"),
 *   cron = {"time" = 60 }
 * )
 */
class TopHeadlinesNewsDownloader extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal service config.factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private ConfigFactoryInterface $configFactory;

  /**
   * Custom service google_news_api.
   *
   * @var \Drupal\google_news_downloader\GoogleNewsAPI2
   */
  private GoogleNewsAPI2 $googleNewsAPI;

  /**
   * Drupal service entity_type.manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private EntityTypeManagerInterface $entityTypeManager;

  public function __construct(array                      $configuration, $plugin_id,
                                                         $plugin_definition,
                              ConfigFactoryInterface     $configFactory,
                              GoogleNewsAPI2             $googleNewsAPI,
                              EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $configFactory;
    $this->googleNewsAPI = $googleNewsAPI;
    $this->entityTypeManager = $entityTypeManager;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('google_news_api'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * Process item.
   *
   * @param $data
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function processItem($data) {
    $top_headlines = $this->googleNewsAPI->getTopHeadLines(
      $data['country'],
      $data['category'],
      $data['q'],
      $data['sources'],
      $data['page_size'],
      $data['page']
    );
    foreach ($top_headlines['articles'] as $article) {
      $google_news = $this->entityTypeManager->getStorage('node')->create([
        'type' => 'google_news',
        'title' => $article['title'],
        'field_remote_image' => [
          'uri' => $article['urlToImage'],
          'alt' => $article['title'],
          'title' => $article['title'],
        ],
        'body' => t('More here @url', ['@url' =>$article['url']]),
        'field_description' => $article['description'],
        'field_external_url' => $article['url'],
        'field_published_at' => strtotime($article['publishedAt']),
        'field_source_name' => $article['source']['name'],
        'field_type' => ['target_id' => 8],
        'langcode' => 'uk',
      ]);
      $google_news->save();
    }
  }

}
