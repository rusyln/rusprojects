<?php

namespace Drupal\aosjs_ui\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures AOS settings.
 */
class AosJsSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'aos_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Attach AOS settings library.
    $form['#attached']['library'][] = 'aosjs_ui/aos-settings';

    // Get current settings.
    $config = $this->config('aosjs.settings');

    $form['settings'] = [
      '#type'  => 'details',
      '#title' => $this->t('Animate settings'),
      '#open'  => TRUE,
    ];

    // Let module handle load AOS.js library.
    $form['settings']['load'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Load AOS library'),
      '#default_value' => $config->get('load'),
      '#description'   => $this->t("If enabled, this module will attempt to load the AOS library for your site. To prevent loading twice, leave this option disabled if you're including the assets manually or through another module or theme."),
    ];

    // Load method library from CDN or Locally.
    $form['settings']['version'] = [
      '#type'          => 'select',
      '#title'         => $this->t('Use version'),
      '#options'       => [
        'v2' => $this->t('Version 2.3.4 (latest stable)'),
        'v3' => $this->t('Version 3.0.0 (latest beta)'),
      ],
      '#default_value' => $config->get('version'),
      '#description'   => $this->t('Select the version of the AOS library you want to use. If you want to use new features like integration with Animate.css library for more animations, you can choose v3.'),
    ];

    // Load method library from CDN or Locally.
    $form['settings']['method'] = [
      '#type'          => 'select',
      '#title'         => $this->t('Attach method'),
      '#options'       => [
        'local' => $this->t('Local'),
        'cdn'   => $this->t('CDN'),
      ],
      '#default_value' => $config->get('method'),
      '#description'   => $this->t('These settings control how the AOS library is loaded. You can choose to load from the CDN (External source) or from the local.'),
    ];

    // Load AOS.js library Per-path.
    $form['settings']['url'] = [
      '#type'        => 'fieldset',
      '#title'       => $this->t('Load on specific URLs'),
      '#collapsible' => TRUE,
      '#collapsed'   => TRUE,
    ];
    $form['settings']['url']['url_visibility'] = [
      '#type'          => 'radios',
      '#title'         => $this->t('Load AOS.js on specific pages'),
      '#options'       => [
        0 => $this->t('All pages except those listed'),
        1 => $this->t('Only the listed pages'),
      ],
      '#default_value' => $config->get('url.visibility'),
    ];
    $form['settings']['url']['url_pages'] = [
      '#type'          => 'textarea',
      '#title'         => '<span class="element-invisible">' . $this->t('Pages') . '</span>',
      '#default_value' => _aosjs_ui_array_to_string($config->get('url.pages')),
      '#description'   => $this->t("Specify pages by using their paths. Enter one path per line. The '*' character is a wildcard. An example path is %admin-wildcard for every user page. %front is the front page.", [
        '%admin-wildcard' => '/admin/*',
        '%front'          => '<front>',
      ]),
    ];

    // AOS.js default options.
    $form['options'] = [
      '#type'  => 'details',
      '#title' => $this->t('AOS default options'),
      '#open'  => TRUE,
    ];

    // The animation to use.
    $form['options']['animation'] = [
      '#type'          => 'select',
      '#options'       => aosjs_animation_options(),
      '#title'         => $this->t('Animation'),
      '#description'   => $this->t('Select the animation name you want to use for CSS selectors globally.'),
      '#default_value' => $config->get('options.animation'),
    ];

    // AOS.js offset.
    $form['options']['offset'] = [
      '#type'          => 'number',
      '#title'         => $this->t('Offset'),
      '#description'   => $this->t('Offset from the original trigger point.'),
      '#default_value' => $config->get('options.offset'),
      '#field_suffix'  => 'px',
      '#attributes'    => ['class' => ['aos-offset']],
    ];

    // AOS.js animation delay.
    $form['options']['delay'] = [
      '#type'          => 'number',
      '#min'           => 0,
      '#title'         => $this->t('Delay'),
      '#description'   => $this->t('Values from 0 to 3000, with step 50ms.'),
      '#default_value' => $config->get('options.delay'),
      '#field_suffix'  => 'ms',
      '#attributes'    => ['class' => ['aos-delay']],
    ];

    // AOS.js animation duration.
    $form['options']['duration'] = [
      '#type'          => 'number',
      '#min'           => 0,
      '#title'         => $this->t('Duration'),
      '#description'   => $this->t('Values from 0 to 3000, with step 50ms.'),
      '#default_value' => $config->get('options.duration'),
      '#field_suffix'  => 'ms',
      '#attributes'    => ['class' => ['aos-duration']],
    ];

    // AOS.js easing functions.
    $form['options']['easing'] = [
      '#type'          => 'select',
      '#options'       => aosjs_easing_functions(),
      '#title'         => $this->t('Easing'),
      '#description'   => $this->t('Default easing for AOS animations.'),
      '#default_value' => $config->get('options.easing'),
    ];

    // AOS.js anchor placements.
    $form['options']['anchor_placement'] = [
      '#type'          => 'select',
      '#options'       => aosjs_anchor_placements(),
      '#title'         => $this->t('Anchor placement'),
      '#description'   => $this->t('Defines which position of the element regarding to window should trigger the animation.'),
      '#default_value' => $config->get('options.anchorPlacement'),
    ];

    // AOS.js once.
    $form['options']['once'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Once'),
      '#description'   => $this->t("Whether animation should happen only once - while scrolling down."),
      '#default_value' => $config->get('options.once'),
    ];

    // AOS.js mirror.
    $form['options']['mirror'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Mirror'),
      '#description'   => $this->t("Whether elements should animate out while scrolling past them."),
      '#default_value' => $config->get('options.mirror'),
      '#states' => [
        'invisible' => [
          'select[name="version"]' => ['value' => 'v2'],
        ],
        'disabled' => [
          ':input[name="version"]' => ['value' => 'v2'],
        ],
      ],
    ];

    // AOS.js default options.
    $form['advanced'] = [
      '#type'  => 'details',
      '#title' => $this->t('AOS advanced settings'),
      '#open'  => FALSE,
    ];

    // AOS.js mirror.
    $form['advanced']['disable'] = [
      '#type'          => 'select',
      '#options'       => aosjs_disable_options(),
      '#title'         => $this->t('Disable'),
      '#description'   => $this->t("Accepts following values: 'phone', 'tablet', 'mobile', boolean, expression or function."),
      '#default_value' => $config->get('advanced.disable'),
    ];

    // AOS.js mirror.
    $form['advanced']['start_event'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Start event'),
      '#description'   => $this->t("Name of the event dispatched on the document, that AOS should initialize on."),
      '#default_value' => $config->get('advanced.startEvent'),
    ];

    // AOS.js default options.
    $form['advanced']['v3'] = [
      '#type'   => 'container',
      '#states' => [
        'invisible' => [
          'select[name="version"]' => ['value' => 'v2'],
        ],
      ],
    ];

    // Class applied after initialization.
    $form['advanced']['v3']['init_class_name'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Initial classname'),
      '#description'   => $this->t("Class applied after initialization."),
      '#default_value' => $config->get('advanced.initClassName'),
      '#states'        => [
        'disabled' => [
          ':input[name="version"]' => ['value' => 'v2'],
        ],
      ],
    ];

    // Class applied on animation.
    $form['advanced']['v3']['animated_class_name'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Animated classname'),
      '#description'   => $this->t("Class applied on animation."),
      '#default_value' => $config->get('advanced.animatedClassName'),
    ];

    // Use classes on scroll.
    $form['advanced']['v3']['use_class_names'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Use classnames'),
      '#description'   => $this->t("If true, will add content of `data-aos` as classes on scroll."),
      '#default_value' => $config->get('advanced.useClassNames'),
    ];

    // Disables automatic mutations.
    $form['advanced']['v3']['disable_mutation_observer'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Disable mutation observer'),
      '#description'   => $this->t("Disables automatic mutations' detections."),
      '#default_value' => $config->get('advanced.disableMutationObserver'),
      '#states'        => [
        'disabled' => [
          ':input[name="version"]' => ['value' => 'v2'],
        ],
      ],
    ];

    // Delay on debounce.
    $form['advanced']['v3']['debounce_delay'] = [
      '#type'          => 'number',
      '#min'           => 0,
      '#title'         => $this->t('Debounce delay'),
      '#description'   => $this->t('The delay on debounce used while resizing window.'),
      '#default_value' => $config->get('advanced.debounceDelay'),
      '#field_suffix'  => 'ms',
      '#states'        => [
        'disabled' => [
          ':input[name="version"]' => ['value' => 'v2'],
        ],
      ],
    ];

    // Delay on throttle.
    $form['advanced']['v3']['throttle_delay'] = [
      '#type'          => 'number',
      '#min'           => 0,
      '#title'         => $this->t('Throttle delay'),
      '#description'   => $this->t('The delay on throttle used while scrolling the page.'),
      '#default_value' => $config->get('advanced.throttleDelay'),
      '#field_suffix'  => 'ms',
      '#states'        => [
        'disabled' => [
          ':input[name="version"]' => ['value' => 'v2'],
        ],
      ],
    ];

    // AOS.js preview.
    $form['preview'] = [
      '#type'  => 'details',
      '#title' => $this->t('Animate preview'),
      '#open'  => TRUE,
    ];

    // AOS.js version warning for preview.
    $form['preview']['warning'] = [
      '#type'   => 'markup',
      '#markup' => '<div class="aos__warning">' . $this->t('The AOS <em>version changed</em> without saving configuration. Please <strong>Save configuration</strong>, so that the preview matches the settings of the selected version.') . '</div>',
    ];

    // AOS.js animation preview.
    $form['preview']['sample'] = [
      '#type'   => 'markup',
      '#markup' => '<div class="aos__preview"><div class="aos__sample">Animate On Scroll!</div></div>',
    ];

    // Replay button for preview AOS.js current configs.
    $form['preview']['replay'] = [
      '#value'      => $this->t('Rebuild'),
      '#type'       => 'button',
      '#attributes' => ['class' => ['aos__replay']],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // @todo Animate field verification.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // Save the updated AOS.js settings.
    $this->config('aosjs.settings')
      ->set('load', $values['load'])
      ->set('version', $values['version'])
      ->set('method', $values['method'])
      ->set('url.visibility', $values['url_visibility'])
      ->set('url.pages', _aosjs_ui_string_to_array($values['url_pages']))
      ->set('options.animation', $values['animation'])
      ->set('options.offset', $values['offset'])
      ->set('options.delay', $values['delay'])
      ->set('options.duration', $values['duration'])
      ->set('options.easing', $values['easing'])
      ->set('options.once', $values['once'])
      ->set('options.mirror', $values['mirror'])
      ->set('options.anchorPlacement', $values['anchor_placement'])
      ->set('advanced.disable', $values['disable'])
      ->set('advanced.startEvent', $values['start_event'])
      ->set('advanced.initClassName', $values['init_class_name'])
      ->set('advanced.animatedClassName', $values['animated_class_name'])
      ->set('advanced.useClassNames', $values['use_class_names'])
      ->set('advanced.disableMutationObserver', $values['disable_mutation_observer'])
      ->set('advanced.debounceDelay', $values['debounce_delay'])
      ->set('advanced.throttleDelay', $values['throttle_delay'])
      ->save();

    // Flush caches so the updated config can be checked.
    drupal_flush_all_caches();

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
