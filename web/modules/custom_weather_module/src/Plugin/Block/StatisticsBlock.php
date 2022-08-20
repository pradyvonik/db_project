<?php

namespace Drupal\custom_weather_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a "Location statistics" block.
 *
 * @Block(
 *   id = "location_statistics",
 *   admin_label = @Translation("Location statistics block"),
 *   category = @Translation("Test focation statistics of thr weather block")
 * )
 */
class StatisticsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Returns database.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Parameters for the __construct method.
   *
   * @param array $configuration
   *   Standard parameter.
   * @param string $plugin_id
   *   Standard parameter.
   * @param string $plugin_definition
   *   Standard parameter.
   * @param \Drupal\Core\Database\Connection $database
   *   Database.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $database) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function build() {
    $result = $this->getLocationStat();
    return [
      '#theme' => 'weather_theme',
      '#header' => 'freakin stat here',
      '#statistics' => $result ?? '',
    ];
  }

  /**
   * Private function that counts users by locations.
   */
  private function uniqueLocations() {
    $result = [];
    // @todo use dependency injection
    // phpcs:ignore
    $connection = $this->database;
    $locations = $connection->select('weather_users_and_locations', 'w')
      ->fields('w', ['location'])
      ->execute()
      ->fetchCol();
    foreach ($locations as $location) {
      if (!in_array($location, $result)) {
        $result[] = $location;
      }
    }
    return $result;
  }

  /**
   * Private function that counts users by locations.
   */
  private function getLocationStat() {
    $result = [];
    $locations = $this->uniqueLocations();
    // @todo use dependency injection
    // phpcs:ignore
    $connection = $this->database;
    foreach ($locations as $location) {
      $count = $connection->select('weather_users_and_locations', 'w')
        ->fields('w')
        ->condition('location', $location)
        ->countQuery()
        ->execute()
        ->fetchField();
      $result[$location] = $count;
    }
    $printed = '';
    arsort($result);
    array_splice($result, 5);
    foreach ($result as $key => $value) {
      $printed .= $key . ' ' . $value . '<br>';
    }
    return $printed;
  }

}
