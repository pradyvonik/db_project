<?php

namespace Drupal\custom_taxonomy_color\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'custom_taxonomy_color' field widget.
 *
 * @FieldWidget(
 *   id = "custom_taxonomy_color_input_widget",
 *   module = "custom_taxonomy_color",
 *   label = @Translation("Custom taxonomy color"),
 *   field_types = {
 *     "custom_taxonomy_color",
 *   }
 * )
 */
class CustomTaxonomyColorInputWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element += [
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->value ?? '',
      '#size' => 7,
      '#maxlength' => 7,
      '#element_validate' => [
        [$this, 'hexColorValidation'],
      ],
    ];
    return ['value' => $element];
  }

  /**
   * {@inheritdoc}
   */
  public function hexColorValidation($element, FormStateInterface $form_state) {
    if (!preg_match('/^#([a-f0-9]{6})$/iD', strtolower($element['#value']))) {
      $form_state->setError($element, 'Color is not in HEX format');
    }
  }

}
