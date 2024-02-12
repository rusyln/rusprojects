<?php

namespace Drupal\aosjs_ui;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\PagerSelectExtender;
use Drupal\Core\Database\Query\TableSortExtender;

/**
 * AOS animate manager.
 */
class AosJsManager implements AosJsManagerInterface {

  /**
   * The database connection used to check the selector against.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a AosJsManager object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection which will be used to check the selector
   *   against.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public function isAnimate($aos) {
    return (bool) $this->connection->query("SELECT * FROM {aos} WHERE [selector] = :selector", [':selector' => $aos])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function loadAnimate() {
    $query = $this->connection
      ->select('aos', 'a')
      ->fields('a', ['aid', 'selector', 'options'])
      ->condition('status', 1);

    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function addAnimate($aos_id, $selector, $label, $comment, $changed, $status, $options) {
    $this->connection->merge('aos')
      ->key('aid', $aos_id)
      ->fields([
        'selector' => $selector,
        'label'    => $label,
        'comment'  => $comment,
        'changed'  => $changed,
        'status'   => $status,
        'options'  => $options,
      ])
      ->execute();

    return $this->connection->lastInsertId();
  }

  /**
   * {@inheritdoc}
   */
  public function removeAnimate($aos_id) {
    $this->connection->delete('aos')
      ->condition('aid', $aos_id)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function findAll($header = [], $search = '', $status = NULL) {
    $query = $this->connection
      ->select('aos', 'a')
      ->extend(PagerSelectExtender::class)
      ->extend(TableSortExtender::class)
      ->orderByHeader($header)
      ->limit(50)
      ->fields('a');

    if (!empty($search) && !empty(trim((string) $search)) && $search !== NULL) {
      $search = trim((string) $search);
      // Escape for LIKE matching.
      $search = $this->connection->escapeLike($search);
      // Replace wildcards with MySQL/PostgreSQL wildcards.
      $search = preg_replace('!\*+!', '%', $search);
      // Add selector and the label field columns.
      $group = $query->orConditionGroup()
        ->condition('selector', '%' . $search . '%', 'LIKE')
        ->condition('label', '%' . $search . '%', 'LIKE');
      // Run the query to find matching targets.
      $query->condition($group);
    }

    // Check if status is set.
    if (!is_null($status) && $status != '') {
      $query->condition('status', $status);
    }

    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function findById($aos_id) {
    return $this->connection->query("SELECT [selector], [label], [comment], [status], [options] FROM {aos} WHERE [aid] = :aid", [':aid' => $aos_id])
      ->fetchAssoc();
  }

}
