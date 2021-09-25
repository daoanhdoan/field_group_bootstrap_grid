<?php

namespace Drupal\field_group_bootstrap_grid\Plugin\field_group\FieldGroupFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Element;
use Drupal\field\FieldConfigInterface;
use Drupal\field_group\FieldGroupFormatterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'bootstrap_grid' formatter.
 *
 * @FieldGroupFormatter(
 *   id = "bootstrap_grid",
 *   label = @Translation("Bootstrap Grid"),
 *   description = @Translation("This fieldgroup renders fields in Bootstrap Grid."),
 *   supported_contexts = {
 *     "form",
 *     "view",
 *   }
 * )
 */
class BootstrapGrid extends FieldGroupFormatterBase
{
  /**
   * Return an array of breakpoint names.
   */
  public static function getBreakpoints()
  {
    return ['xs', 'sm', 'md', 'lg', 'xl'];
  }

  /**
   * {@inheritdoc}
   */
  public function preRender(&$element, $rendering_object)
  {
    parent::preRender($element, $rendering_object);
    $parts = $css_classes = [];

    if (isset($element['#entity_type'])) {
      $parts[] = $element['#entity_type'];
    }
    if (isset($element['#bundle'])) {
      $parts[] = $element['#bundle'];
    }
    if (isset($element['#mode'])) {
      $parts[] = $element['#mode'];
    }
    if ($parts) {
      $css_classes[] = Html::cleanCssIdentifier(implode('-', $parts));
    }
    $element += [
      '#type' => 'field_group_bootstrap_grid',
      '#title' => $this->getLabel(),
      '#attributes' => [
        'class' => implode(" ",
          array_merge(
            $css_classes,
            explode(' ', $this->getSetting('classes'))
          )
        )
      ],
      '#description' => $this->getSetting('description'),
      '#multilingual' => TRUE,
    ];

    // When a fieldset has a description, an id is required.
    if (!$this->getSetting('id')) {
      $element['#id'] = Html::getUniqueId($this->group->group_name);
    }

    if ($this->getSetting('id')) {
      $element['#id'] = Html::getUniqueId($this->getSetting('id'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm()
  {
    $form = parent::settingsForm();
    foreach ($this->getBreakpoints() as $breakpoint) {
      $breakpoint_option = "col_$breakpoint";
      $prefix = 'col' . ($breakpoint != 'xs' ? '-' . $breakpoint : '');
      $form[$breakpoint_option] = [
        '#type' => 'select',
        '#title' => $this->t("Column width of items at '$breakpoint' breakpoint"),
        '#default_value' => $this->getSetting($breakpoint_option),
        '#description' => $this->t("Set the number of columns each item should take up at the '$breakpoint' breakpoint and higher."),
        '#options' => [
          'none' => 'None (or inherit from previous)',
          $prefix => 'Equal',
          $prefix . '-auto' => 'Fit to content',
        ],
      ];
      foreach ([1, 2, 3, 4, 6, 12] as $width) {
        $form[$breakpoint_option]['#options'][$prefix . "-$width"] = $this->formatPlural(12 / $width, '@width (@count column per row)', '@width (@count columns per row)', ['@width' => $width]);
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary()
  {
    $summary = parent::settingsSummary();
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultContextSettings($context)
  {
    foreach (self::getBreakpoints() as $breakpoint) {
      $breakpoint_option = "col_$breakpoint";
      $defaults[$breakpoint_option] = "";
    }
    $defaults += parent::defaultSettings($context);
    return $defaults;
  }

}
