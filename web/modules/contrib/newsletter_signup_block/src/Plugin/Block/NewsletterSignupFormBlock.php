<?php

namespace Drupal\newsletter_signup_block\Plugin\Block;

use Drupal\Component\Utility\EmailValidatorInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilder;
use Drupal\newsletter_signup_block\Form\NewsletterSignupForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Generic Newsletter Signup Form Block.
 *
 * @Block(
 *   id          = "newsletter_signup",
 *   admin_label = @Translation("Newsletter Signup Form"),
 *   category    = @Translation("Newsletter Signup"),
 * )
 */
class NewsletterSignupFormBlock extends BlockBase implements ContainerFactoryPluginInterface, BlockPluginInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Form\FormBuilder definition.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * The email validator.
   *
   * @var \Drupal\Component\Utility\EmailValidatorInterface
   */
  protected $emailValidator;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * NewsletterSignupFormBlock constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   The plugin id.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Extension\ModuleHandler $module_handler
   *   The module handler.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Form\FormBuilder $form_builder
   *   The form builder.
   * @param \Drupal\Component\Utility\EmailValidatorInterface $email_validator
   *   The email validator.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory,
    ModuleHandler $module_handler,
    EntityTypeManagerInterface $entity_type_manager,
    FormBuilder $form_builder,
    EmailValidatorInterface $email_validator,
    LanguageManagerInterface $language_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
    $this->entityTypeManager = $entity_type_manager;
    $this->formBuilder = $form_builder;
    $this->emailValidator = $email_validator;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('entity_type.manager'),
      $container->get('form_builder'),
      $container->get('email.validator'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'title' => '',
      'body' => '',
      'background_image' => NULL,
      'background_image_style' => '',
      'background_responsive_image_style' => '',
      'show_first_name' => FALSE,
      'show_last_name' => FALSE,
      'show_email' => TRUE,
      'method' => 'POST',
      'signup_page' => '',
      'email_placeholder' => 'Email Address',
      'submit_text' => 'Subscribe',
      'show_checkbox_and_text' => FALSE,
      'checkbox_text' => 'Yes please keep me updated with news',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['newsletter_signup'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Signup settings'),
      '#open' => TRUE,
    ];

    $form['newsletter_signup']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => !empty($config['title']) ? $config['title'] : '',
      '#required' => FALSE,
    ];

    $form['newsletter_signup']['body'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Body'),
      '#default_value' => !empty($config['body']['value']) ? $config['body']['value'] : '',
      '#required' => FALSE,
    ];

    $form['newsletter_signup']['background'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Background settings'),
    ];

    $background_image = NULL;
    if (!empty($config['background_image'])) {
      $background_image = $this->entityTypeManager->getStorage('media')->load($config['background_image']);
    }
    $form['newsletter_signup']['background']['background_image'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'media',
      '#selection_handler' => 'default',
      '#selection_settings' => [
        'target_bundles' => ['image'],
      ],
      '#title' => $this->t('Background Image'),
      '#default_value' => $background_image,
      '#required' => FALSE,
    ];

    $image_styles = $this->entityTypeManager->getStorage('image_style')->loadMultiple();
    $background_image_styles = [];
    foreach ($image_styles as $id => $image_style) {
      $background_image_styles[$id] = $image_style->label();
    }
    $form['newsletter_signup']['background']['background_image_style'] = [
      '#type' => 'select',
      '#title' => $this->t('Background image style'),
      '#empty_option' => $this->t('- Select an option -'),
      '#description' => $this->t('Select image style for background image.'),
      '#options' => $background_image_styles,
      '#default_value' => !empty($config['background_image_style']) ? $config['background_image_style'] : '',
      '#required' => FALSE,
    ];

    $responsive_image_styles = $this->entityTypeManager->getStorage('responsive_image_style')->loadMultiple();
    $background_responsive_image_styles = [];
    foreach ($responsive_image_styles as $id => $image_style) {
      $background_responsive_image_styles[$id] = $image_style->label();
    }
    $form['newsletter_signup']['background']['background_responsive_image_style'] = [
      '#type' => 'select',
      '#title' => $this->t('Background responsive image style'),
      '#empty_option' => $this->t('- Select an option -'),
      '#description' => $this->t('Select responsive image style for background image.'),
      '#options' => $background_responsive_image_styles,
      '#default_value' => !empty($config['background_responsive_image_style']) ? $config['background_responsive_image_style'] : '',
      '#required' => FALSE,
    ];

    $form['newsletter_signup_form'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Signup form settings'),
      '#open' => TRUE,
    ];

    $form['newsletter_signup_form']['method'] = [
      '#type' => 'select',
      '#title' => $this->t('Form method'),
      '#empty_option' => $this->t('- Select an option -'),
      '#description' => $this->t('Form method type. In most cases, we should be using POST method.'),
      '#options' => [
        'POST' => $this->t('POST'),
        'GET' => $this->t('GET'),
      ],
      '#default_value' => !empty($config['method']) ? $config['method'] : 'POST',
      '#required' => TRUE,
    ];

    $signup_page = NULL;
    if (!empty($config['signup_page'])) {
      $signup_page = $this->entityTypeManager->getStorage('node')->load($config['signup_page']);
    }

    $form['newsletter_signup_form']['signup_page'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#title' => $this->t('Signup Page'),
      '#description' => $this->t('Select page to be used as the main newsletter signup page.'),
      '#default_value' => $signup_page,
      '#tags' => FALSE,
      '#required' => TRUE,
      '#selection_settings' => [
        'target_bundles' => ['page'],
      ],
    ];

    $form['newsletter_signup_form']['fields'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Form fields'),
      '#open' => FALSE,
    ];

    $form['newsletter_signup_form']['fields']['show_first_name'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show first name field'),
      '#description' => $this->t('Show or hide first name field.'),
      '#default_value' => !empty($config['show_first_name']) ? $config['show_first_name'] : FALSE,
      '#required' => FALSE,
    ];

    $form['newsletter_signup_form']['fields']['show_last_name'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show last name field'),
      '#description' => $this->t('Show or hide last name field.'),
      '#default_value' => !empty($config['show_last_name']) ? $config['show_last_name'] : FALSE,
      '#required' => FALSE,
    ];

    $form['newsletter_signup_form']['fields']['show_email'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show email field'),
      '#description' => $this->t('Show or hide email field. Email field should always be displayed by default.'),
      '#default_value' => !empty($config['show_email']) ? $config['show_email'] : TRUE,
      '#required' => TRUE,
    ];

    $form['newsletter_signup_form']['fields']['email_placeholder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email field placeholder'),
      '#description' => $this->t('Set placeholder for email field.'),
      '#default_value' => !empty($config['email_placeholder']) ? $config['email_placeholder'] : 'Email Address',
      '#required' => FALSE,
    ];

    $form['newsletter_signup_form']['fields']['submit_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Submit button text'),
      '#description' => $this->t('Set submit button text.'),
      '#default_value' => !empty($config['submit_text']) ? $config['submit_text'] : 'Subscribe',
      '#required' => FALSE,
    ];

    $form['newsletter_signup_form']['fields']['show_checkbox_and_text'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show checkbox field'),
      '#description' => $this->t('Show checkbox option with text description.'),
      '#default_value' => !empty($config['show_checkbox_and_text']) ? $config['show_checkbox_and_text'] : FALSE,
      '#required' => FALSE,
    ];

    $form['newsletter_signup_form']['fields']['checkbox_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Checkbox text'),
      '#description' => $this->t('Set Checkbox description text.'),
      '#default_value' => !empty($config['checkbox_text']) ? $config['checkbox_text'] : 'Yes please keep me updated with news',
      '#required' => FALSE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);

    $values = $form_state->getValues();

    $this->configuration['title'] = $values['newsletter_signup']['title'];
    $this->configuration['body'] = $values['newsletter_signup']['body'];

    $this->configuration['background_image'] = $values['newsletter_signup']['background']['background_image'];
    $this->configuration['background_image_style'] = $values['newsletter_signup']['background']['background_image_style'];
    $this->configuration['background_responsive_image_style'] = $values['newsletter_signup']['background']['background_responsive_image_style'];

    $this->configuration['method'] = $values['newsletter_signup_form']['method'];
    $this->configuration['signup_page'] = $values['newsletter_signup_form']['signup_page'];

    $this->configuration['show_first_name'] = $values['newsletter_signup_form']['fields']['show_first_name'];
    $this->configuration['show_last_name'] = $values['newsletter_signup_form']['fields']['show_last_name'];
    $this->configuration['show_email'] = $values['newsletter_signup_form']['fields']['show_email'];

    $this->configuration['email_placeholder'] = $values['newsletter_signup_form']['fields']['email_placeholder'];
    $this->configuration['submit_text'] = $values['newsletter_signup_form']['fields']['submit_text'];

    $this->configuration['show_checkbox_and_text'] = $values['newsletter_signup_form']['fields']['show_checkbox_and_text'];
    $this->configuration['checkbox_text'] = $values['newsletter_signup_form']['fields']['checkbox_text'];
  }

  /**
   * Render responsive image.
   *
   * @param \Drupal\media\Entity\Media|\Drupal\Core\Entity\EntityInterface $image
   *   The media image.
   * @param string $image_style
   *   The image style.
   *
   * @return array|null
   *   The render array.
   */
  private function renderResponsiveImage($image, $image_style) {
    $build = NULL;

    // Set display options.
    $display_options = [
      'label' => 'hidden',
      'type' => 'responsive_image',
      'settings' => [
        'responsive_image_style' => $image_style,
      ],
    ];

    // Get image, apply display options.
    if (!$image->get('field_media_image')->isEmpty()) {
      $build = $image->get('field_media_image')->view($display_options);
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $newsletter_signup_form = new NewsletterSignupForm($this->configuration, $this->entityTypeManager, $this->emailValidator, $this->languageManager);

    $background_image = NULL;
    $background_image_src = NULL;
    if (!empty($this->configuration['background_image'])) {
      $background_image_entity = $this->entityTypeManager->getStorage('media')->load($this->configuration['background_image']);
      if ($background_image_entity) {
        $background_image_uri = $background_image_entity->get('field_media_image')->entity->getFileUri();

        // Background image src url.
        $background_image_src = $this->entityTypeManager->getStorage('image_style')->load($this->configuration['background_image_style'])
          ->buildUrl($background_image_uri);

        // Rendered background image.
        if (!empty($this->configuration['background_responsive_image_style'])) {
          $background_image = $this->renderResponsiveImage($background_image_entity, $this->configuration['background_responsive_image_style']);
        }
      }
    }

    $build = [
      '#theme' => 'newsletter_signup_form_wrapper',
      '#signup_form' => !empty($newsletter_signup_form) ? $this->formBuilder->getForm($newsletter_signup_form) : NULL,
      '#signup_background' => [
        'responsive_image' => $background_image,
        'src' => $background_image_src,
      ],
    ];

    $cacheableMetadata = new CacheableMetadata();
    $cacheableMetadata->addCacheableDependency($this->configuration);
    $cacheableMetadata->applyTo($build);

    return $build;
  }

}
