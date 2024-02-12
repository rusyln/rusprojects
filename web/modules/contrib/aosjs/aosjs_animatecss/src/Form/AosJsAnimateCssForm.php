<?php

namespace Drupal\aosjs_animatecss\Form;

use Drupal\aosjs_ui\Form\AosJsForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * The WOW JS add and edit wow alter on animatecss form.
 *
 * @internal
 */
class AosJsAnimateCssForm extends AosJsForm {

  /**
   * An array to store the variables.
   *
   * @var array
   */
  protected $variables = [];

  /**
   * {@inheritdoc}
   *
   * @param array $form
   *   A nested array form elements comprising the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param int $aos_id
   *   (optional) AOS id to be passed on to
   *   \Drupal::formBuilder()->getForm() for use as the default value of the
   *   AOS ID form data.
   */
  public function buildForm(array $form, FormStateInterface $form_state, int $aos_id = 0) {
    // Retrieve the original form.
    $form = parent::buildForm($form, $form_state, $aos_id);

    // Get current settings.
    $config = $this->config('aosjs.settings');

    if ($config->get('options.library') == 'animate') {
      $form['options']['animation']['#options'] = animatecss_animation_options();
      if ($this->config('animatecss.settings')->get('compat')) {
        $animated_class = 'animated';
      }
      else {
        $animated_class = 'animate__animated';
      }
      $form['advanced']['v3']['use_class_names']['#value'] = TRUE;
      $form['advanced']['v3']['use_class_names']['#default_value'] = TRUE;
      $form['advanced']['v3']['use_class_names']['#disabled'] = TRUE;
    }
    else {
      $form['options']['animation']['#options'] = aosjs_animation_options();
      $animated_class = 'aos-animate';
    }

    $form['advanced']['v3']['animated_class_name']['#value'] = $animated_class;
    $form['advanced']['v3']['animated_class_name']['#default_value'] = $animated_class;
    $form['advanced']['v3']['animated_class_name']['#disabled'] = TRUE;

    return $form;
  }

}
