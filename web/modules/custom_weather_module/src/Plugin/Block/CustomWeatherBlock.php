<?php

namespace Drupal\custom_weather_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;

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
      '#markup' => 'Lutsk is the city',
    ];
  }

}
