<?php

namespace Drupal\aosjs_animatecss\Form;

use Drupal\aosjs_ui\Form\AosJsSettings;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures AOS JS AnimateCSS settings.
 */
class AosJsAnimateCssSettings extends AosJsSettings {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    // Get current settings.
    $config = $this->config('aosjs.settings');

    $form['options']['animation']['#prefix'] = '<div id="animation-options">';
    $form['options']['animation']['#suffix'] = '</div>';
    $form['options']['animation']['#validated'] = TRUE;
    $form['options']['animation']['#needs_validation'] = FALSE;

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
    $form['advanced']['v3']['animated_class_name']['#prefix'] = '<div id="animated-classname">';
    $form['advanced']['v3']['animated_class_name']['#suffix'] = '</div>';

    $form['options']['delay']['#prefix'] = '<div id="delay">';
    $form['options']['delay']['#suffix'] = '</div>';
    $form['options']['duration']['#prefix'] = '<div id="duration">';
    $form['options']['duration']['#suffix'] = '</div>';

    // Load method library from CDN or Locally.
    $form['options']['library'] = [
      '#type'          => 'radios',
      '#title'         => $this->t('Use animations library'),
      '#options'       => [
        'aos'     => $this->t('AOS.js'),
        'animate' => $this->t('Animate.css'),
      ],
      '#default_value' => $config->get('options.library'),
      '#description'   => $this->t('Choose the library whose animations you want to use globally.'),
      '#attributes'    => ['class' => ['aos-library']],
      '#weight'        => -1,
      '#ajax'          => [
        'callback' => '::animationAjaxCallback',
        'wrapper'  => 'animation-options',
        'method'   => 'replace',
        'event'    => 'change',
        'effect'   => 'fade',
        'progress' => ['type' => 'throbber'],
      ],
      '#states'        => [
        'invisible' => [
          'select[name="version"]' => ['value' => 'v2'],
        ],
        'disabled'  => [
          ':input[name="version"]' => ['value' => 'v2'],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function animationAjaxCallback(array &$form, FormStateInterface $form_state) {
    // Set animation options and animate class for selected animation library.
    if ($form_state->getValue('library') == 'animate') {
      $form['options']['animation']['#value'] = 'fadeInUp';
      $form['options']['animation']['#default_value'] = 'fadeInUp';
      $form['options']['animation']['#options'] = animatecss_animation_options();
      if ($this->config('animatecss.settings')->get('compat')) {
        $animated_class = 'animated';
      }
      else {
        $animated_class = 'animate__animated';
      }
      $form['options']['delay']['#value'] = 99;
      $form['options']['duration']['#value'] = 999;

      $form['advanced']['v3']['use_class_names']['#value'] = TRUE;
      $form['advanced']['v3']['use_class_names']['#default_value'] = TRUE;
      $form['advanced']['v3']['use_class_names']['#disabled'] = TRUE;
    }
    else {
      $form['options']['animation']['#value'] = 'fade-up';
      $form['options']['animation']['#default_value'] = 'fade-up';
      $form['options']['animation']['#options'] = aosjs_animation_options();
      $animated_class = 'aos-animate';
      $form['options']['delay']['#value'] = 66;
      $form['options']['duration']['#value'] = 666;
    }

    $form['advanced']['v3']['animated_class_name']['#value'] = $animated_class;
    $form['advanced']['v3']['animated_class_name']['#default_value'] = $animated_class;

    $form_state->setRebuild(TRUE);

    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand("#delay", $form['options']['delay']));
    $response->addCommand(new ReplaceCommand("#duration", $form['options']['duration']));
    $response->addCommand(new ReplaceCommand("#animated-classname", $form['advanced']['v3']['animated_class_name']));
    $response->addCommand(new ReplaceCommand("#use-classnames", $form['advanced']['v3']['use_class_names']));
    $response->addCommand(new ReplaceCommand("#animation-options", $form['options']['animation']));
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // Save the updated WOW.js settings.
    $this->config('aosjs.settings')
      ->set('options.library', $values['library'])
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'aosjs.settings',
    ];
  }

}
