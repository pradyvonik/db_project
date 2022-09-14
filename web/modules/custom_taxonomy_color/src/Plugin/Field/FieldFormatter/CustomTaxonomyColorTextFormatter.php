<?php

namespace Drupal\custom_taxonomy_color\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Component\Render\FormattableMarkup;

/**
 * Plugin implementation of the 'custom_taxonomy_color' field formatter.
 *
 * @FieldFormatter (
 *   id = "custom_taxonomy_color_text_formatter",
 *   label = @Translation("Colored text field"),
 *   field_types = {
 *     "custom_taxonomy_color"
 *   },
 *   )
 */
class CustomTaxonomyColorTextFormatter extends FormatterBase {

  /**
   * {@inheritDoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#type' => 'markup',
        '#markup' => new FormattableMarkup(
          '<div style="color:@color;">@color</div>',
          [
            '@color' => $item->value,
          ]
        ),
      ];
    }

    return $element;
  }

}
