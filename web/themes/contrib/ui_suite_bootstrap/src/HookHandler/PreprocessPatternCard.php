<?php

declare(strict_types = 1);

namespace Drupal\ui_suite_bootstrap\HookHandler;

/**
 * Handle CSS classes.
 */
class PreprocessPatternCard {

  /**
   * Length of the word tabs.
   */
  public const TABS_LENGTH = 4;

  /**
   * Length of the word pills.
   */
  public const PILLS_LENGTH = 5;

  /**
   * Handle CSS classes.
   *
   * @param array $variables
   *   The preprocessed variables.
   */
  public function preprocess(array &$variables): void {
    $this->handleHeaderNavClass($variables);
  }

  /**
   * Handle adding card header class to nav pattern.
   *
   * @param array $variables
   *   The theme key variables.
   */
  protected function handleHeaderNavClass(array &$variables): void {
    if (!\array_key_exists('header', $variables) || !\is_array($variables['header'])) {
      return;
    }

    foreach ($variables['header'] as &$item) {
      $this->addHeaderNavClass($item);
    }
  }

  /**
   * Add expected class in card's header.
   *
   * @param mixed $item
   *   A render item.
   */
  protected function addHeaderNavClass(&$item): void {
    if (!\is_array($item)) {
      return;
    }

    if (\array_key_exists('#id', $item) && \array_key_exists('#variant', $item)) {
      if ($item['#id'] === 'nav') {
        if (\substr($item['#variant'], 0, static::TABS_LENGTH) === 'tabs') {
          $item['#attributes']['class'][] = 'card-header-tabs';
        }
        elseif (\substr($item['#variant'], 0, static::PILLS_LENGTH) === 'pills') {
          $item['#attributes']['class'][] = 'card-header-pills';
        }
      }
    }

    foreach ($item as &$next) {
      $this->addHeaderNavClass($next);
    }
  }

}
