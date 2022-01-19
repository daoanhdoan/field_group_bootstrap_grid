<?php

namespace Drupal\field_group_bootstrap_grid\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\RenderElement;
use Drupal\Core\Template\Attribute;
use Drupal\field_group_bootstrap_grid\Plugin\field_group\FieldGroupFormatter\BootstrapGrid;

/**
 * Provides a render element for bootstrap grid.
 *
 * Formats all child details and all non-child details whose #group is
 * assigned this element's name as grid.
 *
 * @FormElement("field_group_bootstrap_grid")
 */
class FieldGroupBootstrapGrid extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);

    return [
      '#process' => [
        [$class, 'processGroup'],
        [$class, 'processGrid'],
      ],
      '#pre_render' => [
        [$class, 'preRenderGroup'],
      ],
      '#theme_wrappers' => ['field_group_bootstrap_grid'],
    ];
  }

  /**
   * Process the accordion item.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   details element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The processed element.
   */
  public static function processGrid(array &$element, FormStateInterface $form_state) {
    $form = $form_state->getCompleteForm();
    $group_name = $element['#group_name'];
    $group = $form['#fieldgroups'][$group_name];

    $options = !empty($group->format_settings) ? $group->format_settings : [];

    $classes = [];
    foreach (BootstrapGrid::getBreakpoints() as $breakpoint) {
      if ($options["col_$breakpoint"] == 'none') {
        continue;
      }
      $classes[] = $options["col_$breakpoint"];
    }

    foreach ($element['#groups'][$group_name] as &$item) {
      if (!empty($item['#attributes']['class'])) {
        $item['#attributes']['class'] = array_merge($item['#attributes']['class'], $classes);
      }
      if(is_array($item) && empty($item['#attributes']['class'])) {
        $item['#attributes']['class'] = $classes;
      }
    }

    $element['#attached']['library'][] = 'field_group/core';
    return $element;
  }
}
