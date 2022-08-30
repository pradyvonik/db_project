<?php

namespace Drupal\custom_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\media\Plugin\Field\FieldFormatter\MediaThumbnailFormatter;

/**
 * Plugin implementation of the 'media_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "media_formatter",
 *   label = @Translation("Media Formatter that shows only first media"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class FirstMedia extends MediaThumbnailFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    return empty($elements) ? ['#markup' => 'No media was added'] : [
      $elements[0], ['#markup' => 'Few medias was added'],
    ];
  }

}
