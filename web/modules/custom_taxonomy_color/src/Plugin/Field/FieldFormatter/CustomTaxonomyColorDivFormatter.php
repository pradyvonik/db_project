<?php

namespace Drupal\custom_taxonomy_color\Plugin\Field\FieldFormatter;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'custom_taxonomy_color' field formatter.
 *
 * @FieldFormatter(
 *   id = "custom_taxonomy_color_div_formatter",
 *   label = @Translation("Div element with background color"),
 *   field_types = {
 *     "custom_taxonomy_color"
 *   }
 * )
 */
class CustomTaxonomyColorDivFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      // Render each element as markup.
      $element[$delta] = [
        '#type' => 'markup',
        '#markup' => new FormattableMarkup(
          '<div style="background-color: @color;">@color</div>',
          [
            '@color' => $item->value,
          ]
        ),
      ];
    }

    return $element;
  }

}
