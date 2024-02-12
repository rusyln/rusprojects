<?php

namespace Drupal\newsletter_signup_block\Form;

use Drupal\Component\Utility\EmailValidatorInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Newsletter Signup Form.
 *
 * @package Drupal\newsletter_signup_block\Form
 */
class NewsletterSignupForm extends FormBase {

  /**
   * The configuration.
   *
   * @var array
   */
  protected $configuration;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   * NewsletterSignupForm constructor.
   *
   * @param array $configuration
   *   The configuration.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Component\Utility\EmailValidatorInterface $email_validator
   *   The email validator.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(
    array $configuration,
    EntityTypeManagerInterface $entity_type_manager,
    EmailValidatorInterface $email_validator,
    LanguageManagerInterface $language_manager
  ) {
    $this->configuration = $configuration;
    $this->entityTypeManager = $entity_type_manager;
    $this->emailValidator = $email_validator;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'newsletter_signup_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attributes']['class'] = [
      'newsletter-signup__form',
      'js-newsletter-signup-form',
    ];
    $form['#theme'] = 'newsletter_signup_form';

    $form['intro'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['newsletter-signup__intro'],
      ],
    ];

    if (!empty($this->configuration['title'])) {
      $form['intro']['header'] = [
        '#markup' => '<h4>' . $this->configuration['title'] . '</h4>',
      ];
    }

    if (!empty($this->configuration['body']['value'])) {
      $form['intro']['body'] = [
        '#markup' => $this->configuration['body']['value'],
      ];
    }

    $form['fields'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['newsletter-signup__fields'],
      ],
    ];

    // First name field.
    if ($this->configuration['show_first_name']) {
      $form['fields']['first_name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('First Name'),
        '#title_display' => 'invisible',
        '#required' => FALSE,
        '#attributes' => [
          'class' => ['newsletter-signup__text'],
          'placeholder' => $this->t('First Name'),
        ],
      ];
    }

    // Last name field.
    if ($this->configuration['show_last_name']) {
      $form['fields']['last_name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Last Name'),
        '#title_display' => 'invisible',
        '#required' => FALSE,
        '#attributes' => [
          'class' => ['newsletter-signup__text'],
          'placeholder' => $this->t('Last Name'),
        ],
      ];
    }

    // Email field.
    if ($this->configuration['show_email']) {
      $form['fields']['email'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Email Address'),
        '#title_display' => 'invisible',
        '#required' => FALSE,
        '#attributes' => [
          'class' => ['newsletter-signup__email', 'js-newsletter-signup-email'],
          'placeholder' => !empty($this->configuration['email_placeholder']) ? $this->t(':placeholder', [':placeholder' => $this->configuration['email_placeholder']]) : $this->t('Email Address'),
        ],
      ];
    }

    // Checkbox field.
    if ($this->configuration['show_checkbox_and_text'] && !empty($this->configuration['checkbox_text'])) {
      $form['fields']['checkbox_text'] = [
        '#type' => 'checkbox',
        '#title' => $this->t(':title', [':title' => $this->configuration['checkbox_text']]),
        '#title_display' => 'after',
        '#required' => TRUE,
        '#attributes' => [
          'class' => ['newsletter-signup__text'],
        ],
      ];
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#attributes' => [
        'class' => [
          'newsletter-signup__submit',
          'js-newsletter-signup-submit',
        ],
      ],
      '#value' => !empty($this->configuration['submit_text']) ? $this->t(':submit', [':submit' => $this->configuration['submit_text']]) : $this->t('Subscribe'),
    ];

    $form['#attached']['library'][] = 'newsletter_signup_block/newsletter_signup';

    // No cache.
    $cacheableMetadata = new CacheableMetadata();
    $cacheableMetadata->setCacheMaxAge(0);
    $cacheableMetadata->applyTo($form);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('email');
    if (!empty($email)) {
      if (!$this->emailValidator->isValid($email)) {
        $form_state->setErrorByName('email', $this->t('Email address is not valid.'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if (!empty($this->configuration['signup_page'])) {
      $signup_page = $this->entityTypeManager->getStorage('node')->load($this->configuration['signup_page']);
      if ($signup_page) {
        $args = ['node' => $signup_page->id()];
        $options = [];
        $language = $this->languageManager->getCurrentLanguage();
        if (!empty($language)) {
          $options['language'] = $language;
        }
        $form_state->setRedirect('entity.node.canonical', $args, $options);
      }
    }
  }

}
