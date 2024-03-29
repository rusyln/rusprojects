<?php

namespace Drupal\Tests\ds\Functional;

use Drupal\block_content\Entity\BlockContent;
use Drupal\user\Entity\User;

/**
 * Tests for the manage display tab in Display Suite.
 *
 * @group ds
 */
class BlockTest extends TestBase {

  use DsTestTrait;

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = [
    'node',
    'user',
    'comment',
    'field_ui',
    'block',
    'block_content',
    'ds',
  ];

  /**
   * The created user.
   *
   * @var User
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create a test user.
    $this->adminUser = $this->drupalCreateUser([
      'access content',
      'admin display suite',
      'admin fields',
      'administer blocks',
      'administer block types',
      'administer block content',
      'administer block_content display',
    ]);
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Test adding a block, modifying output.
   */
  public function testBlock() {

    // Create basic block type.
    $edit = [
      'label' => 'Basic Block',
      'id' => 'basic',
    ];
    $this->drupalGet('admin/structure/block-content/add');
    $this->submitForm($edit, 'Save');
    $this->assertSession()->pageTextContains('Block type Basic Block has been added.');

    // Create a basic block.
    $edit = [];
    $edit['info[0][value]'] = 'Test Block';
    $edit['body[0][value]'] = $this->randomMachineName(16);
    $this->drupalGet('block/add/basic', []);
    $this->submitForm($edit, t('Save'));
    $this->assertSession()->pageTextContains('Basic Block Test Block has been created.');

    // Place the block.
    $instance = [
      'id' => 'testblock',
      'settings[label]' => $edit['info[0][value]'],
      'region' => 'sidebar_first',
    ];
    $block = BlockContent::load(1);
    $url = 'admin/structure/block/add/block_content:' . $block->uuid() . '/' . $this->config('system.theme')->get('default');
    $this->drupalGet($url);
    $this->submitForm($instance, t('Save block'));

    // Change to a DS layout.
    $url = 'admin/structure/block-content/manage/basic/display';
    $edit = ['ds_layout' => 'ds_2col'];
    $this->drupalGet($url, []);
    $this->submitForm($edit, t('Save'));

    $fields = [
      'fields[block_description][region]' => 'left',
      'fields[body][region]' => 'right',
    ];
    $this->dsConfigureUi($fields, 'admin/structure/block-content/manage/basic/display');

    // View the block.
    $this->drupalGet('<front>');
    $this->assertSession()->pageTextContains('Test Block');
    $xpath = $this->xpath('//div[@class="region region-sidebar-first"]/div/div[@class="block-content block-content--type-basic block-content--view-mode-full ds-2col clearfix"]/div[@class="group-left"]/div[@class="field field--name-block-description field--type-ds field--label-hidden field__item"]/h2');
    $this->assertEquals(count($xpath), 1, 'Description in group-left');
    $xpath = $this->xpath('//div[@class="region region-sidebar-first"]/div/div[@class="block-content block-content--type-basic block-content--view-mode-full ds-2col clearfix"]/div[@class="group-right"]/div[@class="clearfix text-formatted field field--name-body field--type-text-with-summary field--label-hidden field__item"]/p');
    $this->assertEquals(count($xpath), 1, 'Body in group-right');
  }

}
