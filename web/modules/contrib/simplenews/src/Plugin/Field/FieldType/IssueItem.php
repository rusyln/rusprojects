<?php

namespace Drupal\simplenews\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\MapDataDefinition;

/**
 * Defines the 'issue' entity field type (extended entity_reference).
 *
 * Supported settings (below the definition's 'settings' key) are:
 * - target_type: The entity type to reference. Required.
 * - target_bundle: (optional): If set, restricts the entity bundles which may
 *   may be referenced. May be set to an single bundle, or to an array of
 *   allowed bundles.
 * - handler: The issue handler.
 * - handler_settings: The issue handler settings.
 * - status: A flag indicating whether the issue is published (3), ready (2),
 *   pending (1) or not (0).
 * - sent_count: Counter of already sent newsletters.
 * - error_count: Counter of send errors.
 * - subscribers: Counter of subscribers.
 *
 * @FieldType(
 *   id = "simplenews_issue",
 *   label = @Translation("Simplenews issue"),
 *   description = @Translation("An entity field containing an extended entityreference."),
 *   no_ui = TRUE,
 *   default_widget = "simplenews_issue",
 *   list_class = "\Drupal\Core\Field\EntityReferenceFieldItemList"
 * )
 */
class IssueItem extends EntityReferenceItem {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Call the parent to define the target_id and entity properties.
    $properties = parent::propertyDefinitions($field_definition);

    $properties['handler'] = DataDefinition::create('string')
      ->setLabel(t('Handler'));

    $properties['handler_settings'] = MapDataDefinition::create()
      ->setLabel(t('Handler settings'));

    $properties['status'] = DataDefinition::create('integer')
      ->setLabel(t('Status'))
      ->setSetting('unsigned', TRUE);

    $properties['sent_count'] = DataDefinition::create('integer')
      ->setLabel(t('Sent count'))
      ->setSetting('unsigned', TRUE);

    $properties['error_count'] = DataDefinition::create('integer')
      ->setLabel(t('Error count'))
      ->setSetting('unsigned', TRUE);

    $properties['subscribers'] = DataDefinition::create('integer')
      ->setLabel(t('Subscribers'))
      ->setSetting('unsigned', TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);

    $schema['columns']['handler'] = [
      'description' => 'The issue handler.',
      'type' => 'varchar',
      'length' => 255,
      'not null' => FALSE,
    ];
    $schema['columns']['handler_settings'] = [
      'description' => 'The issue handler settings.',
      'type' => 'blob',
      'size' => 'big',
      'not null' => FALSE,
      'serialize' => TRUE,
    ];
    $schema['columns']['status'] = [
      'description' => 'A flag indicating whether the issue is published (3), ready (2), pending (1) or not (0).',
      'type' => 'int',
      'size' => 'tiny',
      'not null' => FALSE,
    ];
    $schema['columns']['sent_count'] = [
      'description' => 'Counter of already sent newsletters.',
      'type' => 'int',
      'unsigned' => TRUE,
      'not null' => FALSE,
    ];
    $schema['columns']['error_count'] = [
      'description' => 'Counter of already sent newsletters.',
      'type' => 'int',
      'unsigned' => TRUE,
      'not null' => FALSE,
    ];
    $schema['columns']['subscribers'] = [
      'description' => 'Counter of subscribers.',
      'type' => 'int',
      'unsigned' => TRUE,
      'not null' => FALSE,
    ];
    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function setValue($values, $notify = TRUE) {
    if (count($values) == 1 && isset($values['target_id'])) {
      $values = array_merge($this->values, $values);

      if (!isset($values['handler']) || $values['handler'] == NULL) {
        $values['handler'] = 'simplenews_all';
      }
      if (!isset($values['status']) || $values['status'] == NULL) {
        $values['status'] = 0;
      }
      if (!isset($values['sent_count']) || $values['sent_count'] == NULL) {
        $values['sent_count'] = 0;
      }
      if (!isset($values['error_count']) || $values['error_count'] == NULL) {
        $values['error_count'] = 0;
      }
      if (!isset($values['subscribers']) || $values['subscribers'] == NULL) {
        $values['subscribers'] = 0;
      }
    }
    parent::setValue($values, $notify);
  }

}
