<?php

namespace Drupal\aosjs_ui;

use Drupal\aosjs_ui\Form\AosJsFilter;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Displays AOS JS selector.
 *
 * @internal
 */
class AosJsAdmin extends FormBase {

  /**
   * Animate manager.
   *
   * @var \Drupal\aosjs_ui\AosJsManagerInterface
   */
  protected $animateManager;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * Constructs a new Animate object.
   *
   * @param \Drupal\aosjs_ui\AosJsManagerInterface $animate_manager
   *   The Animate selector manager.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Symfony\Component\HttpFoundation\Request $current_request
   *   The current request.
   */
  public function __construct(AosJsManagerInterface $animate_manager, DateFormatterInterface $date_formatter, FormBuilderInterface $form_builder, Request $current_request) {
    $this->animateManager = $animate_manager;
    $this->dateFormatter  = $date_formatter;
    $this->formBuilder    = $form_builder;
    $this->currentRequest = $current_request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('aosjs.animate_manager'),
      $container->get('date.formatter'),
      $container->get('form_builder'),
      $container->get('request_stack')->getCurrentRequest(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'aos_admin_form';
  }

  /**
   * {@inheritdoc}
   *
   * @param array $form
   *   A nested array form elements comprising the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Attach AOS JS admin list library.
    $form['#attached']['library'][] = 'aosjs_ui/aos-list';

    $search = $this->currentRequest->query->get('search');
    $status = $this->currentRequest->query->get('status') ?? NULL;

    /** @var \Drupal\aosjs_ui\Form\AosJsFilter $form */
    $form['aos_admin_filter_form'] = $this->formBuilder->getForm(AosJsFilter::class, $search, $status);
    $form['#attributes']['class'][] = 'aos-filter';
    $form['#attributes']['class'][] = 'views-exposed-form';

    $header = [
      [
        'data'  => $this->t('Selector'),
        'field' => 'a.aid',
      ],
      [
        'data'  => $this->t('Label'),
        'field' => 'a.label',
      ],
      [
        'data'  => $this->t('Status'),
        'field' => 'a.status',
      ],
      [
        'data'  => $this->t('Updated'),
        'field' => 'a.changed',
        'sort'  => 'desc',
      ],
      $this->t('Operations'),
    ];

    $rows = [];
    $result = $this->animateManager->findAll($header, $search, $status);
    foreach ($result as $animate) {
      $row = [];
      $row[] = $animate->selector;
      $row[] = $animate->label;
      $row[] = $animate->status ? $this->t('Enabled') : $this->t('Disabled');
      $row[] = $this->dateFormatter->format($animate->changed, 'short');
      $links = [];
      $links['edit'] = [
        'title' => $this->t('Edit'),
        'url'   => Url::fromRoute('aosjs.edit', ['aos_id' => $animate->aid]),
      ];
      $links['delete'] = [
        'title' => $this->t('Delete'),
        'url'   => Url::fromRoute('aosjs.delete', ['aos_id' => $animate->aid]),
      ];
      $links['duplicate'] = [
        'title' => $this->t('Duplicate'),
        'url'   => Url::fromRoute('aosjs.duplicate', ['aos_id' => $animate->aid]),
      ];
      $row[] = [
        'data' => [
          '#type'  => 'operations',
          '#links' => $links,
        ],
      ];
      $rows[] = $row;
    }

    $form['aos_admin_table'] = [
      '#type'   => 'table',
      '#header' => $header,
      '#rows'   => $rows,
      '#empty'  => $this->t('No animate CSS selector available. <a href=":link">Add animate</a> .', [
        ':link' => Url::fromRoute('aosjs.add')
          ->toString(),
      ]),
    ];

    $form['pager'] = ['#type' => 'pager'];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // @todo Add operations to AOS admin form.
  }

}
