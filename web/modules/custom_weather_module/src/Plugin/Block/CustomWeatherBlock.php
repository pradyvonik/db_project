<?php

namespace Drupal\custom_weather_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\custom_weather_module\WeatherData;

/**
 * Provides a "Custom weather module" block.
 *
 * @Block(
 *   id = "custom_weather_block",
 *   admin_label = @Translation("Custom weather block"),
 *   category = @Translation("Test weather block for the DB project")
 * )
 */
class CustomWeatherBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Get data from Drupal\custom_weather_module\WeatherData service.
   *
   * @var array
   */
  protected $data;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
          $plugin_id,
          $plugin_definition,
    WeatherData $data
  ) {
    $this->data = $data;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('custom_weather_module.get_data'),
    );
  }

  /**
   * {@inheritDoc}
   */
  public function build() {
    $weather = $this->data->getWeather();
    $location = $this->data->userLocation();
    // $number is used to check if cache works properly.
    $number = rand(0, 100);
    return [
      '#theme' => 'weather_theme',
      '#header' => 'Actual weather:' . $number,
      '#user_location' => $location ?? '',
      '#conditions' => $weather['conditions'] ?? '',
      '#icon' => $weather['icon'] ?? '',
      '#cache' => [
        'contexts' => ['location'],
        'max-age' => 3600,
      ],
    ];
  }

}
