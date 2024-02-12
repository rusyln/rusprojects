<?php

namespace Drupal\Tests\newsletter_signup_block\Functional;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Test the Newsletter Signup Block module install without errors.
 *
 * @group newsletter_signup_block
 */
class InstallTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['newsletter_signup_block'];

  /**
   * Assert that the newsletter_signup_block module installed correctly.
   */
  public function testModuleInstalls() {
    // If we get here, then the module was successfully installed during the
    // setUp phase without throwing any Exceptions. Assert that TRUE is true,
    // so at least one assertion runs, and then exit.
    $this->assertTrue(TRUE, 'Module installed correctly.');
  }

}
