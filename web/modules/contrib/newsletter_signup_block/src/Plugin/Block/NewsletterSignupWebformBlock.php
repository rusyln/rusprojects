<?php

namespace Drupal\newsletter_signup_block\Plugin\Block;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilder;
use Drupal\webform\WebformInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Newsletter signup webform block.
 *
 * @Block(
 *   id          = "newsletter_signup_webform",
 *   admin_label = @Translation("Newsletter Signup Webform"),
 *   category    = @Translation("Newsletter Signup"),
 * )
 */
class NewsletterSignupWebformBlock extends BlockBase implements ContainerFactoryPluginInterface, BlockPluginInterface {

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
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory,
    ModuleHandler $module_handler,
    EntityTypeManagerInterface $entity_type_manager,
    FormBuilder $form_builder
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
    $this->entityTypeManager = $entity_type_manager;
    $this->formBuilder = $form_builder;
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
      $container->get('form_builder')
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
      'webform_id' => '',
    ];
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

    $form['newsletter_signup']['webform_id'] = [
      '#title' => $this->t('Webform'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'webform',
      '#required' => TRUE,
      '#default_value' => $this->getWebform(),
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

    $this->configuration['webform_id'] = $values['newsletter_signup']['webform_id'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

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
      '#theme' => 'newsletter_signup_webform_wrapper',
      '#signup_title' => $this->configuration['title'],
      '#signup_body' => !empty($this->configuration['body']['value']) ? $this->configuration['body']['value'] : NULL,
      '#signup_form' => [
        '#type' => 'webform',
        '#webform' => $this->getWebform(),
      ],
      '#signup_background' => [
        'responsive_image' => $background_image,
        'src' => $background_image_src,
      ],
    ];

    $build['#attached']['library'][] = 'newsletter_signup_block/newsletter_signup';

    $cacheableMetadata = new CacheableMetadata();
    $cacheableMetadata->addCacheableDependency($this->configuration);
    $cacheableMetadata->applyTo($build);

    return $build;
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
   *   Returns the render array.
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
  protected function blockAccess(AccountInterface $account) {
    $webform = $this->getWebform();
    if (!$webform) {
      return AccessResult::forbidden();
    }

    $access_result = $webform->access('submission_create', $account, TRUE);
    if ($access_result->isAllowed()) {
      return $access_result;
    }

    $has_access_denied_message = ($webform->getSetting('form_access_denied') !== WebformInterface::ACCESS_DENIED_DEFAULT);
    return AccessResult::allowedIf($has_access_denied_message)
      ->addCacheableDependency($access_result);
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = parent::calculateDependencies();

    $webform = $this->getWebform();
    $dependencies[$webform->getConfigDependencyKey()][] = $webform->getConfigDependencyName();

    return $dependencies;
  }

  /**
   * Get this block instance webform.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   Returns the webform entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getWebform() {
    return $this->entityTypeManager->getStorage('webform')
      ->load($this->configuration['webform_id']);
  }

}
