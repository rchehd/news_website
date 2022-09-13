<?php

namespace Drupal\google_news_downloader;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Http\ClientFactory;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Session\AccountInterface;

class GoogleNewsAPI {

  /**
   * API url for headline news.
   */
  const HEADLINE_URL = 'https://newsapi.org/v2/top-headlines?';

  /**
   * API url for everything news.
   */
  const EVERYTHING_URL = 'https://newsapi.org/v2/everything?';

  /**
   * Drupal service config.factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private ConfigFactoryInterface $configFactory;

  /**
   * Google News API.
   *
   * @var array|mixed|null
   */
  private mixed $apiKey;

  /**
   * Drupal service current_user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  private AccountInterface $currentUser;

  /**
   * Drupal service entity_type.manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private EntityTypeManagerInterface $entityTypeManager;

  /**
   * Drupal service language.manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  private LanguageManagerInterface $languageManager;

  /**
   * Drupal service http_client_factory.
   *
   * @var \Drupal\Core\Http\ClientFactory
   */
  private ClientFactory $clientFactory;

  /**
   * Drupal service logger.factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  private LoggerChannelInterface $logger;

  public function __construct(ConfigFactoryInterface     $configFactory,
                              AccountInterface           $currentUser,
                              EntityTypeManagerInterface $entityTypeManager,
                              LanguageManagerInterface   $languageManager ,
                              ClientFactory              $clientFactory,
                              LoggerChannelFactoryInterface $logger) {
    $this->configFactory = $configFactory;
    $this->apiKey = $configFactory->get('google_news_downloader.settings')->get('google_news_api_key');
    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entityTypeManager;
    $this->languageManager = $languageManager;
    $this->clientFactory = $clientFactory;
    $this->logger = $logger->get('google_news_api');
  }

  /**
   * Get top headlines news (one of parameters are required).
   *
   * @param string $country
   *   The 2-letter ISO 3166-1 code of the country you want to get headlines for. US is the default
   * @param null $category
   *   The category you want to get headlines for.
   * @param null $q
   *   Keywords or a phrase to search for.
   * @param null $sources
   *   A comma-seperated string of identifiers for the news sources or blogs you want headlines from
   * @param null $page_size
   *   The number of results to return per page (request). 20 is the default, 100 is the maximum.
   * @param null $page
   *   Use this to page through the results if the total results found is greater than the page size.
   *
   * @return mixed
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getTopHeadLines(string $country='en', $category=null, $q=null, $sources=null, $page_size=null, $page=null): mixed {
    // Add default country.
    $uri = $this::HEADLINE_URL . 'country=' . $country . '&';

    // Add category if existed.
    if (!is_null($category)) {
      $uri = $uri . 'category=' . $category . '&';
    }

    // Add category if existed.
    if (!is_null($q)) {
      $uri = $uri . 'category=' . $q . '&';
    }

    // Add sources if existed and category not existed.
    if (!is_null($sources) && is_null($category)) {
      $uri = $uri . 'sources=' . $sources . '&';
    }

    // Add page_size if existed.
    if (!is_null($page_size)) {
      $uri = $uri . 'page_size=' . $page_size . '&';
    }

    // Add page if existed and page_size if existed.
    if (!is_null($page) && !is_null($page_size)) {
      $uri = $uri . 'page=' . $page . '&';
    }

    // Add api key from configs.
    if (!is_null($this->apiKey)) {
      $uri = $uri . 'apiKey=' . $this->apiKey . '&';
    }
    else {
      $this->logger->error('Google News API key is empty');
      return [];
    }

    $response = $this->clientFactory
      ->fromOptions()
      ->request('GET', $uri);

    if ($response->getStatusCode() == 200) {
      return json_decode($response->getBody()->getContents(), TRUE);
    }
    else {
      $response_body = json_encode($response->getBody());
      $this->logger->error($response_body);
      return [];
    }
  }

  /**
   * @param $q
   *   Keywords or phrases to search for in the article title and body.
   *   Advanced search is supported here:
   *         - Surround phrases with quotes (") for exact match.
   *         - Prepend words or phrases that must appear with a + symbol. Eg: +bitcoin
   *         - Prepend words that must not appear with a - symbol. Eg: -bitcoin
   *         - Alternatively you can use the AND / OR / NOT keywords, and optionally group these with parenthesis. Eg: crypto AND (ethereum OR litecoin) NOT bitcoin.
   *         The complete value for q must be URL-encoded. Max length: 500 chars.
   * @param $searchIn
   *    The fields to restrict your q search to.
   *           The possible options are:
   *              - title
   *              - description
   *              - content
   *           Multiple options can be specified by separating them with a comma, for example: title,content.
   *           This parameter is useful if you have an edge case where searching all the fields is not giving the desired outcome, but generally you should not need to set this.
   *           Default: all fields are searched.
   * @param $sources
   *   A comma-seperated string of identifiers (maximum 20) for the news sources or blogs you want headlines from.
   * @param $domains
   *   A comma-seperated string of domains (e.g. bbc.co.uk, tech-crunch.com, gadget.com) to restrict the search to.
   * @param $exclude_domains
   *   A comma-seperated string of domains (e.g. bbc.co.uk, tech-crunch.com, gadget.com) to remove from the results.
   * @param $from
   *   A date and optional time for the oldest article allowed. This should be in ISO 8601 format (e.g. 2022-09-13 or 2022-09-13T11:04:45)
   * @param $to
   *   A date and optional time for the newest article allowed. This should be in ISO 8601 format (e.g. 2022-09-13 or 2022-09-13T11:04:45)
   * @param $language
   *   The 2-letter ISO-639-1 code of the language you want to get headlines for.
   * @param $sort_by
   *   The order to sort the articles in. Possible options: relevancy, popularity, publishedAt.
   *      relevancy = articles more closely related to q come first.
   *      popularity = articles from popular sources and publishers come first.
   *      publishedAt = the newest articles come first.
   *      Default: publishedAt.
   * @param $page_size
   *   The number of results to return per page. Default: 100. Maximum: 100.
   * @param $page
   *  Use this to page through the results. Default: 1.
   * @return array|mixed
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getEverything($q, $searchIn=null, $sources=null, $domains=null, $exclude_domains=null, $from=null, $to=null, $language='en', $sort_by=null, $page_size=null, $page=null): mixed {
    // Add default country.
    $uri = $this::EVERYTHING_URL . 'q=' . $q . '&';

    // Add searchIn if existed.
    if (!is_null($searchIn)) {
      $uri = $uri . 'searchIn=' . $searchIn . '&';
    }

    // Add searchIn if existed.
    if (!is_null($sources)) {
      $uri = $uri . 'sources=' . $sources . '&';
    }

    // Add domains if existed.
    if (!is_null($domains)) {
      $uri = $uri . 'domains=' . $domains . '&';
    }

    // Add excludeDomains if existed.
    if (!is_null($exclude_domains)) {
      $uri = $uri . 'excludeDomains=' . $exclude_domains . '&';
    }

    // Add from if existed.
    if (!is_null($from)) {
      $uri = $uri . 'from=' . $from . '&';
    }

    // Add to if existed.
    if (!is_null($to)) {
      $uri = $uri . 'to=' . $to . '&';
    }

    // Add language if existed.
    if (!is_null($language)) {
      $uri = $uri . 'language=' . $language . '&';
    }

    // Add sortBy if existed.
    if (!is_null($sort_by)) {
      $uri = $uri . 'sortBy=' . $sort_by . '&';
    }

    // Add page_size if existed.
    if (!is_null($page_size)) {
      $uri = $uri . 'page_size=' . $page_size . '&';
    }

    // Add page if existed and page_size if existed.
    if (!is_null($page) && !is_null($page_size)) {
      $uri = $uri . 'page=' . $page . '&';
    }

    // Add api key from configs.
    if (!is_null($this->apiKey)) {
      $uri = $uri . 'apiKey=' . $this->apiKey . '&';
    }
    else {
      $this->logger->error('Google News API key is empty');
      return [];
    }

    $response = $this->clientFactory
      ->fromOptions()
      ->request('GET', $uri);

    if ($response->getStatusCode() == 200) {
      return json_decode($response->getBody()->getContents(), TRUE);
    }
    else {
      $response_body = json_encode($response->getBody());
      $this->logger->error($response_body);
      return [];
    }

  }

}
