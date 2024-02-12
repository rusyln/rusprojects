<?php

namespace Drupal\aosjs_ui\Form;

use Drupal\aosjs_ui\AosJsManagerInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * AOS JS add and edit animate form.
 *
 * @internal
 */
class AosJsForm extends FormBase {

  /**
   * Animate manager.
   *
   * @var \Drupal\aosjs_ui\AosJsManagerInterface
   */
  protected $animateManager;

  /**
   * A config object for the AOS settings.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Constructs a new AOS object.
   *
   * @param \Drupal\aosjs_ui\AosJsManagerInterface $animate_manager
   *   The AOS animate manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A config factory for retrieving required config objects.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(AosJsManagerInterface $animate_manager, ConfigFactoryInterface $config_factory, TimeInterface $time) {
    $this->animateManager = $animate_manager;
    $this->config = $config_factory->get('aosjs.settings');
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('aosjs.animate_manager'),
      $container->get('config.factory'),
      $container->get('datetime.time'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'aos_form';
  }

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
    // Attach AOS form library.
    $form['#attached']['library'][] = 'aosjs_ui/aos-form';

    // Get AOS data by ID.
    $aos = $this->animateManager->findById($aos_id) ?? [];

    // Set stored AOS data.
    $selector = $aos['selector'] ?? '';
    $label    = $aos['label'] ?? '';
    $comment  = $aos['comment'] ?? '';
    $status   = $aos['status'] ?? TRUE;
    $options  = [];

    // Handle the case when $aos is not an array or option is not set.
    if (is_array($aos) && isset($aos['options'])) {
      $options = unserialize($aos['options'], ['allowed_classes' => FALSE]) ?? '';
    }

    // Store animate id.
    $form['aos_id'] = [
      '#type'  => 'value',
      '#value' => $aos_id,
    ];

    // Load the AOS JS configuration settings.
    $config = $this->config;

    // Get version of AOS from settings.
    $version = $config->get('version');

    // The default selector.
    $form['selector'] = [
      '#title'         => $this->t('Selector'),
      '#type'          => 'textfield',
      '#required'      => TRUE,
      '#size'          => 64,
      '#maxlength'     => 256,
      '#default_value' => $selector,
      '#description'   => $this->t('Enter a valid element or a css selector.'),
    ];

    // The label of this selector.
    $form['label'] = [
      '#title'         => $this->t('Label'),
      '#type'          => 'textfield',
      '#required'      => FALSE,
      '#size'          => 64,
      '#maxlength'     => 64,
      '#default_value' => $label ?? '',
      '#description'   => $this->t('The label for this aos selector like <em>About block</em>.'),
    ];

    // AOS options.
    $form['options'] = [
      '#title' => $this->t('AOS options'),
      '#type'  => 'details',
      '#open'  => TRUE,
    ];

    // The animation to use.
    $form['options']['animation'] = [
      '#type'          => 'select',
      '#options'       => aosjs_animation_options(),
      '#title'         => $this->t('Animation'),
      '#description'   => $this->t('Select the animation name you want to use for CSS selector.'),
      '#default_value' => $options['animation'] ?? $config->get('options.animation'),
    ];

    // AOS.js offset.
    $form['options']['offset'] = [
      '#type'          => 'number',
      '#title'         => $this->t('Offset'),
      '#description'   => $this->t('Offset from the original trigger point.'),
      '#default_value' => $options['offset'] ?? $config->get('options.offset'),
      '#field_suffix'  => 'px',
      '#attributes'    => ['class' => ['aos-offset']],
    ];

    // AOS.js animation delay.
    $form['options']['delay'] = [
      '#type'          => 'number',
      '#min'           => 0,
      '#max'           => 3000,
      '#title'         => $this->t('Delay'),
      '#description'   => $this->t('Values from 0 to 3000, with step 50ms.'),
      '#default_value' => $options['delay'] ?? $config->get('options.delay'),
      '#field_suffix'  => 'ms',
      '#attributes'    => ['class' => ['aos-delay']],
    ];

    // AOS.js animation duration.
    $form['options']['duration'] = [
      '#type'          => 'number',
      '#min'           => 0,
      '#max'           => 3000,
      '#title'         => $this->t('Duration'),
      '#description'   => $this->t('Values from 0 to 3000, with step 50ms.'),
      '#default_value' => $options['duration'] ?? $config->get('options.duration'),
      '#field_suffix'  => 'ms',
      '#attributes'    => ['class' => ['aos-duration']],
    ];

    // AOS.js easing functions.
    $form['options']['easing'] = [
      '#type'          => 'select',
      '#options'       => aosjs_easing_functions(),
      '#title'         => $this->t('Easing'),
      '#description'   => $this->t('Default easing for AOS animations.'),
      '#default_value' => $options['easing'] ?? $config->get('options.easing'),
    ];

    // AOS.js anchor placements.
    $form['options']['anchor_placement'] = [
      '#type'          => 'select',
      '#options'       => aosjs_anchor_placements(),
      '#title'         => $this->t('Anchor placement'),
      '#description'   => $this->t('Defines which position of the element regarding to window should trigger the animation.'),
      '#default_value' => $options['anchorPlacement'] ?? $config->get('options.anchorPlacement'),
    ];

    // AOS.js anchor for version 2.
    if ($version == 'v2') {
      $form['options']['anchor'] = [
        '#type'          => 'textfield',
        '#title'         => $this->t('Anchor'),
        '#description'   => $this->t("Anchor element, whose offset will be counted to trigger animation instead of actual elements offset."),
        '#default_value' => $options['anchor'] ?? '',
      ];
    }

    // AOS.js once.
    $form['options']['once'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Once'),
      '#description'   => $this->t("Whether animation should happen only once - while scrolling down."),
      '#default_value' => $options['once'] ?? $config->get('options.once'),
    ];

    // AOS.js mirror for version 3.
    if ($version == 'v3') {
      $form['options']['mirror'] = [
        '#type'          => 'checkbox',
        '#title'         => $this->t('Mirror'),
        '#description'   => $this->t("Whether elements should animate out while scrolling past them."),
        '#default_value' => $options['mirror'] ?? $config->get('options.mirror'),
        '#states'        => [
          'invisible' => [
            'select[name="version"]' => ['value' => 'v2'],
          ],
          'disabled'  => [
            ':input[name="version"]' => ['value' => 'v2'],
          ],
        ],
      ];
    }

    // AOS.js preview.
    $form['preview'] = [
      '#type'  => 'details',
      '#title' => $this->t('Animate preview'),
      '#open'  => TRUE,
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

    // The comment for describe animate settings and usage in website.
    $form['comment'] = [
      '#type'          => 'textarea',
      '#title'         => $this->t('Comment'),
      '#description'   => $this->t('Describe this animate settings and usage in your website.'),
      '#default_value' => $comment ?? '',
      '#rows'          => 2,
      '#weight'        => 96,
    ];

    // Enabled status for this animate.
    $form['status'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Enabled'),
      '#description'   => $this->t('Animate will appear on pages that have this selector.'),
      '#default_value' => $status ?? TRUE,
      '#weight'        => 99,
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type'        => 'submit',
      '#button_type' => 'primary',
      '#value'       => $this->t('Save'),
      '#submit'      => [[$this, 'submitForm']],
    ];

    if ($aos_id != 0) {
      // Add a 'Remove' button for animate form.
      $form['actions']['delete'] = [
        '#type'       => 'link',
        '#title'      => $this->t('Delete'),
        '#url'        => Url::fromRoute('aosjs.delete', ['aos_id' => $aos_id]),
        '#attributes' => [
          'class' => [
            'action-link',
            'action-link--danger',
            'action-link--icon-trash',
          ],
        ],
      ];

      // Redirect to list for submit handler on edit form.
      $form['actions']['submit']['#submit'] = ['::submitForm', '::overview'];
    }
    else {
      // Add a 'Save and go to list' button for add form.
      $form['actions']['overview'] = [
        '#type'   => 'submit',
        '#value'  => $this->t('Save and go to list'),
        '#submit' => array_merge($form['actions']['submit']['#submit'], ['::overview']),
        '#weight' => 20,
      ];
    }

    return $form;
  }

  /**
   * Submit handler for removing animate.
   *
   * @param array[] $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function remove(&$form, FormStateInterface $form_state) {
    $aos_id = $form_state->getValue('aos_id');
    $form_state->setRedirect('aosjs.delete', ['aos_id' => $aos_id]);
  }

  /**
   * Form submission handler for the 'overview' action.
   *
   * @param array[] $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function overview(array $form, FormStateInterface $form_state): void {
    $form_state->setRedirect('aosjs.admin');
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $aos_id   = $form_state->getValue('aos_id');
    $is_new   = $aos_id == 0;
    $selector = trim($form_state->getValue('selector'));

    if ($is_new) {
      if ($this->animateManager->isAnimate($selector)) {
        $form_state->setErrorByName('selector', $this->t('This selector is already exists.'));
      }
    }
    else {
      if ($this->animateManager->findById($aos_id)) {
        $animate = $this->animateManager->findById($aos_id);

        if ($selector != $animate['selector'] && $this->animateManager->isAnimate($selector)) {
          $form_state->setErrorByName('selector', $this->t('This selector is already added.'));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Load the AOS JS configuration settings.
    $config = $this->config;

    // Get all form field values.
    $values = $form_state->getValues();

    // Set main AOS database column data.
    $aos_id   = $values['aos_id'];
    $selector = trim($values['selector']);
    $label    = trim($values['label']);
    $comment  = trim($values['comment']);
    $status   = $values['status'];

    // Provide a label from selector if was empty.
    if (empty($label)) {
      $label = ucfirst(trim(preg_replace("/[^a-zA-Z0-9]+/", " ", $selector)));
    }

    // Set variables from main AOS settings.
    $variables['animation']       = $values['animation'];
    $variables['offset']          = $values['offset'];
    $variables['delay']           = $values['delay'];
    $variables['duration']        = $values['duration'];
    $variables['easing']          = $values['easing'];
    $variables['anchorPlacement'] = $values['anchor_placement'];
    $variables['anchor']          = $values['anchor'] ?? '';
    $variables['once']            = $values['once'];
    $variables['mirror']          = $values['mirror'] ?? $config->get('options.mirror');

    // Serialize options variables.
    $options = serialize($variables);

    // The Unix timestamp when the animate was most recently saved.
    $changed = $this->time->getCurrentTime();

    // Save animate.
    $this->animateManager->addAnimate($aos_id, $selector, $label, $comment, $changed, $status, $options);
    $this->messenger()
      ->addStatus($this->t('The selector %selector has been added.', ['%selector' => $selector]));

    // Flush caches so the updated config can be checked.
    drupal_flush_all_caches();
  }

}
