<?php

namespace Drupal\custom_weather_module;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;

/**
 * Cache context ID: 'location'.
 */
class LocationCacheContext implements CacheContextInterface {

  /**
   * Drupal\custom_weather_module\WeatherData instance.
   *
   * @var \Drupal\custom_weather_module\WeatherData
   */
  protected $location;

  /**
   * Construct Drupal\custom_weather_module\WeatherData.
   *
   * @param \Drupal\custom_weather_module\WeatherData $location
   *   Drupal\custom_weather_module\WeatherData object.
   */
  public function __construct(WeatherData $location) {
    $this->location = $location;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('location');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    return $this->location->userLocation();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    return new CacheableMetadata();
  }

}
