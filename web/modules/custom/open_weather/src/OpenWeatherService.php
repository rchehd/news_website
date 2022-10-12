<?php

namespace Drupal\open_weather;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Http\ClientFactory;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\Core\TempStore\PrivateTempStoreFactory;

class OpenWeatherService {

  /**
   * Open Weather API url.
   */
  const OPEN_WEATHER_URL = 'api.openweathermap.org/data/2.5/weather?';

  /**
   * Open Weather API url.
   */
  const OPEN_WEATHER_FORECAST = 'api.openweathermap.org/data/2.5/forecast?';

  /**
   * @var string
   */
  private string $api_key;

  /**
   * @var \Drupal\Core\Http\ClientFactory
   */
  private ClientFactory $clientFactory;

  /**
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  private LoggerChannelInterface $logger;

  /**
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  private PrivateTempStore $tempstore;

  /**
   * {@inheritDoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory,
                              ClientFactory $clientFactory,
                              LoggerChannelFactoryInterface $logger,
                              PrivateTempStoreFactory $temp_store_factory) {
    $this->api_key = $config_factory->get('open_weather.settings')->get('api_key');
    $this->clientFactory = $clientFactory;
    $this->logger = $logger->get('open_weather');
    $this->tempstore = $temp_store_factory->get('open_weather');
  }

  /**
   * Get current weather.
   *
   * @param $ip
   *   IP address.
   * @return array
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getCurrentWeather($ip): array {
    $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
    $city = $details->city ?? 'London';
    $uri = $this::OPEN_WEATHER_URL . 'q=' . $city . '&units=metric&APPID=' . $this->api_key;
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
   * Get forecast.
   *
   * @param $ip
   *   IP address.
   *
   * @return array
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  public function getForecast($ip): array {
    $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
    $city = $details->city ?? 'London';
    if($this->tempstore->get($city)) {
      return $this->tempstore->get($city);
    }
    $uri = $this::OPEN_WEATHER_FORECAST . 'q=' . $city . '&units=metric&APPID=' . $this->api_key;
    $response = $this->clientFactory
      ->fromOptions()
      ->request('GET', $uri);
    if ($response->getStatusCode() == 200) {
      $result = json_decode($response->getBody()->getContents(), TRUE);
      $this->tempstore->set($city, $result);
      return $result;
    }
    else {
      $response_body = json_encode($response->getBody());
      $this->logger->error($response_body);
      return [];
    }

  }
}
