<?php

namespace Drupal\aosjs_ui\Form;

use Drupal\aosjs_ui\AosJsManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a form to remove CSS selector.
 *
 * @internal
 */
class AosJsDelete extends ConfirmFormBase {

  /**
   * The AOS animate.
   *
   * @var array
   */
  protected $aos;

  /**
   * The AOS animate manager.
   *
   * @var \Drupal\aosjs_ui\AosJsManagerInterface
   */
  protected $animateManager;

  /**
   * Constructs a new AosDelete object.
   *
   * @param \Drupal\aosjs_ui\AosJsManagerInterface $animate_manager
   *   The AOS animate manager.
   */
  public function __construct(AosJsManagerInterface $animate_manager) {
    $this->animateManager = $animate_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('aosjs.animate_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'aos_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to remove %selector from AOS selectors?', ['%selector' => $this->aos['selector']]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   *
   * @param array $form
   *   A nested array form elements comprising the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param int $aos_id
   *   The AOS record ID to remove.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $aos_id = 0) {
    if (!$this->aos = $this->animateManager->findById($aos_id)) {
      throw new NotFoundHttpException();
    }
    $form['aos_id'] = [
      '#type'  => 'value',
      '#value' => $aos_id,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $aos_id = $form_state->getValue('aos_id');
    $this->animateManager->removeAnimate($aos_id);
    $this->logger('user')
      ->notice('Deleted %selector', ['%selector' => $this->aos['selector']]);
    $this->messenger()
      ->addStatus($this->t('The AOS selector %selector was deleted.', ['%selector' => $this->aos['selector']]));

    // Flush caches so the updated config can be checked.
    drupal_flush_all_caches();

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('aosjs.admin');
  }

}
