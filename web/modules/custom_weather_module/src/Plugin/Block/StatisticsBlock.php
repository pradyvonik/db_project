<?php

namespace Drupal\custom_weather_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a "Location statistics" block.
 *
 * @Block(
 *   id = "location_statistics",
 *   admin_label = @Translation("Location statistics block"),
 *   category = @Translation("Test focation statistics of thr weather block")
 * )
 */
class StatisticsBlock extends BlockBase {

  /**
   * {@inheritDoc}
   */
  public function build() {
    return [
      '#theme' => 'weather_theme',
      '#header' => 'freakin stat here',
      '#table' => 'TABLE',
    ];
  }

}
