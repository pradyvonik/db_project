<?php

namespace Drupal\custom_weather_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

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
   * Returns link.
   *
   * @var \Drupal\Core\Link
   */
  protected $link;

  /**
   * Returns URI.
   *
   * @var \Drupal\Core\Url
   */
  protected $uri;

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
  public function __construct(array $configuration,
  $plugin_id,
  $plugin_definition,
  Connection $database) {
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
   * Private function that makes array of the top-5 locations.
   */
  private function topLocations() {
    $result = [];
    $locations = $this->uniqueLocations();
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
    arsort($result);
    array_splice($result, 5);
    return array_keys($result);
  }

  /**
   * Private function that shows users from the top-5 locations.
   */
  private function getLocationStat() {
    $connection = $this->database;
    $query = $connection->select('weather_users_and_locations', 'w');
    $query->innerJoin('users_field_data', 'u', 'u.uid = w.uid');
    $query->fields('w', ['location']);
    $query->fields('u', ['name', 'uid']);
    $results = $query->execute()->fetchAll();
    $locations = $this->topLocations();
    $counter = 1;
    foreach ($locations as $location) {
      $printed .= '<br>' . $counter++ . ". " . $location . ': ';
      $links = [];
      foreach ($results as $result) {
        if ($result->location == $location && $result->uid) {
          $links[] = Link::fromTextAndUrl($result->name, Url::fromUri("base:/user/{$result->uid}"))->toString();
          // $links[] = "<a href=user/{$result->uid}>{$result->name}</a>";
        }
      }
      $printed .= implode(', ', $links);
    }
    return $printed;
  }

}
