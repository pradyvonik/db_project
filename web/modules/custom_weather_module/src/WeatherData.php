<?php

namespace Drupal\custom_weather_module;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountProxyInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Drupal\Core\Logger\LoggerChannelFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides data for a "Custom weather module" block.
 */
class WeatherData {

  /**
   * Returns config file.
   *
   * @var \Drupal\Core\Config\ConfigFactory
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
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * Logs error messages.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $logger;

  /**
   * Parameters for the __construct method.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   Config.
   * @param \Drupal\Core\Database\Connection $database
   *   Database.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   Current user.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   Request.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger
   *   Cache.
   */
  public function __construct(
    ConfigFactory $config,
    Connection $database,
    AccountProxyInterface $currentUser,
    RequestStack $request,
    LoggerChannelFactory $logger
  ) {
    $this->config = $config->get('custom_weather_module.settings');
    $this->database = $database;
    $this->currentUser = $currentUser;
    $this->request = $request->getCurrentRequest();
    $this->logger = $logger->get('custom_weather_module');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('database'),
      $container->get('current_user'),
      $container->get('request_stack'),
      $container->get('logger.factory'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'api_key' => $this->config->get('api_key'),
      'location' => $this->config->get('location'),
    ];
  }

  /**
   * Private function for getting user IP.
   */
  public function userLocation() {
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
      $this->logger->error('Message from @module: @message', [
        '@module' => 'custom_weather_module',
        '@message' => "GuzzleHttp\Exception\ConnectException: cURL error 6: Could not resolve host: ip-api.com",
      ]);
      $location = NULL;
    }
    if ($set = $this->config
      ->get('location')) {
      $location = $set;
    }
    $this->writeToTableUsers($location);
    return $location;
  }

  /**
   * Private function that gets weather info from weatherapi.com.
   */
  public function getWeather() {
    if ($this->config->get('api_key')) {
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
      $this->logger->error('Message from @module: @message', [
        '@module' => 'custom_weather_module',
        '@message' => "GuzzleHttp\Exception\ConnectException: cURL error 6: Could not resolve host: weatherapi.com",
      ]);
      return NULL;
    }

  }

  /**
   * Private function that gets user locations and put them into database.
   */
  public function writeToTableUsers($location) {
    $connection = $this->database;
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
