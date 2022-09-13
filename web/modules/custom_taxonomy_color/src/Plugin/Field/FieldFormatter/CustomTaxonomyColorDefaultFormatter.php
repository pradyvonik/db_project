<?php

namespace Drupal\custom_taxonomy_color\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'custom_taxonomy_color' field formatter.
 *
 * @FieldFormatter (
 *   id = "custom_taxonomy_color_default_formatter",
 *   label = @Translation("Return HEX"),
 *   field_types = {
 *     "custom_taxonomy_color"
 *   },
 *   )
 */
class CustomTaxonomyColorDefaultFormatter extends FormatterBase {

  /**
   * {@inheritDoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#type' => 'markup',
        '#markup' => $item->value,
      ];
    }

    return $element;
  }

}
