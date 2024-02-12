<?php

declare(strict_types = 1);

namespace Drupal\ui_suite_bootstrap\HookHandler;

use Drupal\block_content\BlockContentInterface;

/**
 * Pre-processes variables for the "block" theme hook.
 */
class PreprocessBlock {

  /**
   * Add class if the block is not published.
   *
   * Done in a preprocess because block content does not have a dedicated
   * template.
   *
   * @param array $variables
   *   The preprocessed variables.
   */
  public function preprocess(array &$variables): void {
    if (isset($variables['content']['#block_content']) && $variables['content']['#block_content'] instanceof BlockContentInterface) {
      /** @var \Drupal\block_content\BlockContentInterface $block_content */
      $block_content = $variables['content']['#block_content'];
      if (!$block_content->isPublished()) {
        $variables['attributes']['class'][] = 'is-unpublished';
      }
    }
  }

}
