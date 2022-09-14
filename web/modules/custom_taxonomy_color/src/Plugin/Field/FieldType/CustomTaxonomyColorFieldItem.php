<?php

namespace Drupal\custom_taxonomy_color\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'custom_taxonomy_color' field type.
 *
 * @FieldType(
 *   id = "custom_taxonomy_color",
 *   label = @Translation("Custom color field"),
 *   module = "custom_taxonomy_color",
 *   description = @Translation("Custom color picker."),
 *   category = @Translation("Color"),
 *   default_widget = "custom_taxonomy_color_input_widget",
 *   default_formatter = "custom_taxonomy_color_default_formatter"
 * )
 */
class CustomTaxonomyColorFieldItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'value' => [
          'type' => 'text',
          'size' => 'tiny',
          'not null' => FALSE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('string')
      ->setLabel(t('Hex color'));

    return $properties;
  }

}
