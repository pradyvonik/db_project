<?php

namespace Drupal\custom_weather_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Provides a "Custom weather module" block.
 *
 * @Block(
 *   id = "custom_weather_block",
 *   admin_label = @Translation("Custom weather block"),
 *   category = @Translation("Test weather block for the DB project")
 * )
 */
class CustomWeatherBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    // @todo use dependency injection
    // phpcs:ignore
    $default_config = \Drupal::config('custom_weather_module.settings');
    return [
      'api_key' => $default_config->get('api_key'),
      'location' => $default_config->get('location'),
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function build() {
    $weather = $this->userWeather();
    return [
      '#theme' => 'weather_theme',
      '#weather_header' => 'Weather now in ',
      '#user_location' => $this->userLocation() ?? '',
      '#conditions' => $weather['conditions'] ?? '',
      '#icon' => $weather['icon'] ?? '',
    ];

  }

  /**
   * Private function for getting user IP.
   */
  private function userLocation() {
    // @todo fix 2 problems here:
    // 1. rewrite $user_ip request with dependency injection
    // 2. make the function get proper user IP that could be geo located
    // random IP from the Netherlands is being used down here
    // phpcs:ignore
    $user_ip = \Drupal::request()->getClientIp();
    $client = new Client();
    try {
      $request = $client->get('http://ip-api.com/json/' . $user_ip);
      $response = json_decode($request->getBody(), TRUE);
      if ($response['status'] == 'success') {
        $location = $response['city'] . ", " . $response['country'];
      }
    }
    catch (RequestException $e) {
      // @todo use dependency injection
      // phpcs:ignore
      \Drupal::logger('custom_weather_module')->error('Message from @module: @message', [
        '@module' => 'custom_weather_module',
        '@message' => "GuzzleHttp\Exception\ConnectException: cURL error 6: Could not resolve host: ip-api.com",
      ]);
      $location = NULL;
    }
    // @todo use dependency injection
    // phpcs:ignore
    if ($set = \Drupal::config('custom_weather_module.settings')
      ->get('location')) {
      // @todo use dependency injection
      // phpcs:ignore
      $location = $set;
    }
    $this->writeToTable($location);
    return $location;
  }

  /**
   * Private function that gets weather info from weatherapi.com.
   */
  private function getWeather() {
    // @todo use dependency injection
    // phpcs:ignore
    if (\Drupal::config('custom_weather_module.settings')->get('api_key')) {
      // @todo use dependency injection
      // phpcs:ignore
      $api_key = \Drupal::config('custom_weather_module.settings')->get('api_key');
    }
    else {
      $api_key = '1f5e3134f1dd43b9bfc150946221008';
    }
    $location = $this->userLocation();
    $client = new Client();
    try {
      $request = $client->get('http://api.weatherapi.com/v1/current.json?key=' . $api_key . '&q=' . $location);
      $response = json_decode($request->getBody(), TRUE);
      return [
        'conditions' => $response['current']['temp_c'] . ' °C, ' . $response['current']['condition']['text'],
        'icon' => $response['current']['condition']['icon'],
      ];
    }
    catch (RequestException $e) {
      // @todo use dependency injection
      // phpcs:ignore
      \Drupal::logger('custom_weather_module')->error('Message from @module: @message', [
        '@module' => 'custom_weather_module',
        '@message' => "GuzzleHttp\Exception\ConnectException: cURL error 6: Could not resolve host: weatherapi.com",
      ]);
      return NULL;
    }

  }

  /**
   * Private function that gets data from cache or sets it.
   */
  private function userWeather() {
    $cacheId = "weatherCache:{$this->userLocation()}";
    // @todo use dependency injection
    // phpcs:ignore
    $cache = \Drupal::cache()->get($cacheId);
    $weatherData = $this->getWeather();
    if ($cache
      && ($cache->data['conditions'])) {
      return $cache->data;
    }
    else {
      $data = [
        'conditions' => $weatherData['conditions'],
        'icon' => $weatherData['icon'],
      ];
      // @todo use dependency injection
      // phpcs:ignore
      \Drupal::cache()->set($cacheId, $data,time() + 3600);
      return $weatherData;
    }
  }

  /**
   * Private function that gets user locations and put them into database.
   */
  private function writeToTable($location) {
    // @todo use dependency injection
    // phpcs:ignore
    $connection = \Drupal::database();
    // phpcs:ignore
    $user = \Drupal::currentUser()->id();
    if ($connection->select('custom_weather_module', 'w')
      ->fields('w')
      ->condition('uid', $user)
      ->condition('location', $location)
      ->execute()
      ->fetchAssoc()) {
      return TRUE;
    }
    else {
      $newData = [
        'uid' => $user,
        'location' => $location,
      ];
      return $connection->insert('custom_weather_module')
        ->fields($newData)
        ->execute();
    }
  }

}
