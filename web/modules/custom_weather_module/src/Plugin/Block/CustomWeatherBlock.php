<?php

namespace Drupal\custom_weather_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Database\Connection;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

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
   * Returns config file.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Returns database.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Returns current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Makes http request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Returns cache.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Logs error messages.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Parameters for the __construct method.
   *
   * @param array $configuration
   *   Standard parameter.
   * @param string $plugin_id
   *   Standard parameter.
   * @param string $plugin_definition
   *   Standard parameter.
   * @param \Drupal\Core\Config\ImmutableConfig $config
   *   Config.
   * @param \Drupal\Core\Database\Connection $database
   *   Database.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   Current user.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Cache.
   * @param \Psr\Log\LoggerInterface $logger
   *   Cache.
   */
  public function __construct(
    array $configuration,
          $plugin_id,
          $plugin_definition,
    ImmutableConfig $config,
    Connection $database,
    AccountProxyInterface $currentUser,
    Request $request,
    CacheBackendInterface $cache,
    LoggerInterface $logger
  ) {
    $this->config = $config;
    $this->database = $database;
    $this->currentUser = $currentUser;
    $this->request = $request;
    $this->cache = $cache;
    $this->logger = $logger;
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
      $container->get('config.factory')->get('custom_weather_module.settings'),
      $container->get('database'),
      $container->get('current_user'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('cache.default'),
      $container->get('logger.factory')->get('custom_weather_module'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    // @todo use dependency injection
    // phpcs:ignore
//    $default_configs = \Drupal::config('custom_weather_module.settings');
    return [
      'api_key' => $this->config->get('api_key'),
      'location' => $this->config->get('location'),
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function build() {
    $weather = $this->userWeather();
    $location = $this->userLocation();
    return [
      '#theme' => 'weather_theme',
      '#header' => 'Actual weather:',
      '#user_location' => $location ?? '',
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
//    $user_ip = \Drupal::request()->getClientIp();
    $user_ip = $this->request->getClientIp();
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
      //      \Drupal::logger('custom_weather_module')->error('Message from @module: @message', [
      $this->logger->error('Message from @module: @message', [
        '@module' => 'custom_weather_module',
        '@message' => "GuzzleHttp\Exception\ConnectException: cURL error 6: Could not resolve host: ip-api.com",
      ]);
      $location = NULL;
    }
    // @todo use dependency injection
    // phpcs:ignore
//    if ($set = \Drupal::config('custom_weather_module.settings')
    if ($set = $this->config
      ->get('location')) {
      // @todo use dependency injection
      // phpcs:ignore
      $location = $set;
    }
    $this->writeToTableUsers($location);
    return $location;
  }

  /**
   * Private function that gets weather info from weatherapi.com.
   */
  private function getWeather() {
    // @todo use dependency injection
    // phpcs:ignore
//    if (\Drupal::config('custom_weather_module.settings')->get('api_key')) {
    if ($this->config->get('api_key')) {
      // @todo use dependency injection
      // phpcs:ignore
//      $api_key = \Drupal::config('custom_weather_module.settings')->get('api_key');
      $api_key = $this->config->get('api_key');
    }
    else {
      return NULL;
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
      //      \Drupal::logger('custom_weather_module')->error('Message from @module: @message', [
      $this->logger->error('Message from @module: @message', [
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
//    $cache = \Drupal::cache()->get($cacheId);
    $cache = $this->cache->get($cacheId);
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
      $this->cache->set($cacheId, $data,time() + 3600);
      return $weatherData;
    }
  }

  /**
   * Private function that gets user locations and put them into database.
   */
  private function writeToTableUsers($location) {
    // @todo use dependency injection
    // phpcs:ignore
//    $connection = \Drupal::database();
    $connection = $this->database;
    // phpcs:ignore
//    $user = \Drupal::currentUser()->id();
    $user = $this->currentUser->id();
    if ($connection->select('weather_users_and_locations', 'w')
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
      return $connection->insert('weather_users_and_locations')
        ->fields($newData)
        ->execute();
    }
  }

}
