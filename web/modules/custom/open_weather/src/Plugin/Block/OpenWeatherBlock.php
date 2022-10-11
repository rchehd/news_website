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
  public function build(): array {
    // Get weather forecast.
    $weather = $this->open_weather->getForecast($_SERVER['REMOTE_ADDR']);
    $data = [];
    if ($weather) {
      $data['city'] = $weather['city']['name'] . ' ' . $this->t('weather');
      $data['list'] = [];
      // Temporary arrays to create final array.
      $temp = [];
      $image = [];
      foreach ($weather['list'] as $item) {
        $temp[date('D', $item['dt'])][date('G', $item['dt']) . ':00'] = $item['main']['temp'];
        $image[date('D', $item['dt'])][$item['main']['temp']] = 'https://openweathermap.org/img/w/' . $item['weather'][0]['icon'] . '.png';
      }
      
      foreach ($temp as $key => $item) {
        $data['list'][] = [
          'day' => $key,
          'max_temp' => round(max($item),0) . '°C',
          'min_temp' => round(min($item), 0) . '°C',
          'icon_url' => $image[$key][intval(max($item))],
        ];
      }
    }
    return [
      '#theme'    => 'open_weather',
      '#data'     => $data,
      '#attached' => [
        'library' => ['open_weather/open_weather'],
      ],
      '#cache' => [
        'max-age' => 360,
        'contexts' => ['user'],
      ],
    ];

  }

}
