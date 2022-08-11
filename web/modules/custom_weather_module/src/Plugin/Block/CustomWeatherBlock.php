<?php

namespace Drupal\custom_weather_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use GuzzleHttp\Client;

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
   * {@inheritDoc}
   */
  public function build() {
    return [
      '#markup' => 'Your location is: ' . $this->userLocation() . '. The weather is: ' . $this->userWeather(),
    ];
  }

  /**
   * Private function for getting user IP.
   */
  private function userLocation() {
    // $user_ip = \Drupal::request()->getClientIp();
    // @todo fix 2 problems here:
    // 1. rewrite $user_ip request with dependency injection
    // 2. make the function get proper user IP that could be geo located
    // random IP from the Netherlands is being used down here
    $user_ip = '50.7.93.84';
    $client = new Client();
    $request = $client->get('http://ip-api.com/json/' . $user_ip);
    $response = json_decode($request->getBody(), TRUE);
    if ($response['status'] == 'success') {
      $location = $response['city'] . ", " . $response['country'];
    }
    else {
      $location = 'Lutsk,Ukraine';
    }
    return $location;
  }

  /**
   * Private function that gets weather info from weatherapi.com.
   */
  private function userWeather() {
    $api_key = '1f5e3134f1dd43b9bfc150946221008';
    $location = $this->userLocation();
    $client = new Client();
    $request = $client->get('http://api.weatherapi.com/v1/current.json?key=' . $api_key . '&q=' . $location);
    $response = json_decode($request->getBody(), TRUE);
    return $response['current']['temp_c'] . ' °C, ' . $response['current']['condition']['text'];
  }

}
