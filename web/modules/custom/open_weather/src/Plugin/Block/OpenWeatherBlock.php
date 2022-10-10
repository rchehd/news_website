<?php
namespace Drupal\open_weather\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\open_weather\OpenWeatherService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Top Headlines Block.
 *
 * @Block(
 *   id = "open_weather_block",
 *   admin_label = @Translation("Open weather Block"),
 *   category = @Translation("Weather block"),
 * )
 */
class OpenWeatherBlock extends BlockBase implements ContainerFactoryPluginInterface{

  /**
   * @var \Drupal\open_weather\OpenWeatherService
   */
  private OpenWeatherService $open_weather;

  /**
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, OpenWeatherService $open_weather) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->open_weather = $open_weather;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('open_weather'),
    );
  }

  /**
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function build() {
    $weather = $this->open_weather->getForecast($_SERVER['REMOTE_ADDR']);
    $data = [];
    if ($weather) {
      $data['city'] = $weather['city']['name'];
      $data['list'] = [];
      foreach ($weather['list'] as $item) {
        $data['list'][date('l', $item['dt'])] = [
          'temp' => $item['main']['temp'],
          'time' => date('l', $item['dt']),
          'icon_url' => 'https://openweathermap.org/img/w/' . $item['weather'][0]['icon'] . '.png',
        ];
      }
//      $data['current_temp'] = round($weather['main']['temp'], 0) . '°C';
//      $data['feel_temp'] = round($weather['main']['feels_like'], 0) . '°C';
//      $data['icon'] = $weather['weather'][0]['icon'];
//      $data['text'] = $this->t('Now');

    }
    return [
      '#theme'    => 'open_weather',
      '#data'     => $data,
      '#attached' => [
        'library' => ['open_weather/open_weather'],
      ],
    ];

  }

}
