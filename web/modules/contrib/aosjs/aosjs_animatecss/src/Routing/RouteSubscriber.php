<?php

namespace Drupal\aosjs_animatecss\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('aosjs.settings')) {
      $route->setDefault('_form', 'Drupal\aosjs_animatecss\Form\AosJsAnimateCssSettings');
    }
    if ($route = $collection->get('aosjs.add')) {
      $route->setDefault('_form', 'Drupal\aosjs_animatecss\Form\AosJsAnimateCssForm');
    }
    if ($route = $collection->get('aosjs.edit')) {
      $route->setDefault('_form', 'Drupal\aosjs_animatecss\Form\AosJsAnimateCssForm');
    }
  }

}
