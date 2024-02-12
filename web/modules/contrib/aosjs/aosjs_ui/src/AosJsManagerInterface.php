<?php

namespace Drupal\aosjs_ui;

/**
 * Provides an interface defining an AOS animate manager.
 */
interface AosJsManagerInterface {

  /**
   * Returns if this AOS css selector is added.
   *
   * @param string $aos
   *   The AOS css selector to check.
   *
   * @return bool
   *   TRUE if the AOS js selector is added, FALSE otherwise.
   */
  public function isAnimate($aos);

  /**
   * Finds all enabled aos selectors.
   *
   * @return string|false
   *   Either the enabled aos selector or FALSE.
   */
  public function loadAnimate();

  /**
   * Add an AOS js selector.
   *
   * @param int $aos_id
   *   The AOS id for edit.
   * @param string $selector
   *   The AOS selector to add.
   * @param string $label
   *   The label of AOS selector.
   * @param string $comment
   *   The comment for AOS options.
   * @param int $changed
   *   The expected modification time.
   * @param int $status
   *   The status for AOS.
   * @param string $options
   *   The AOS selector options.
   *
   * @return int|null|string
   *   The last insert ID of the query, if one exists.
   */
  public function addAnimate($aos_id, $selector, $label, $comment, $changed, $status, $options);

  /**
   * Remove an AOS css selector.
   *
   * @param int $aos_id
   *   The AOS id to remove.
   */
  public function removeAnimate($aos_id);

  /**
   * Finds all added AOS css selector.
   *
   * @param array $header
   *   The AOS header to sort selector and label.
   * @param string $search
   *   The AOS search key to filter selector.
   * @param int|null $status
   *   The AOS status to filter selector.
   *
   * @return \Drupal\Core\Database\StatementInterface
   *   The result of the database query.
   */
  public function findAll($header, $search, $status);

  /**
   * Finds an added AOS js selector by its ID.
   *
   * @param int $aos_id
   *   The ID for an added AOS selector.
   *
   * @return string|false
   *   Either the added AOS selector or FALSE if none exist with that ID.
   */
  public function findById($aos_id);

}
