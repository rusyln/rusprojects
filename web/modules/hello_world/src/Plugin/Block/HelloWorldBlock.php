<?php

namespace Drupal\hello_world\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Hello World Block' block.
 *
 * @Block(
 *   id = "hello_world_block",
 *   admin_label = @Translation("Hello World Block"),
 *   category = @Translation("Custom block for hello world")
 * )
 */
class HelloWorldBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'youtube_video_id' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['youtube_video_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('YouTube Video ID'),
      '#default_value' => $this->configuration['youtube_video_id'],
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['youtube_video_id'] = $form_state->getValue('youtube_video_id');
  }
 
  /**
   * {@inheritdoc}
   */
  public function build() {
    // Fetch YouTube video ID from block configuration.
    $video_id = $this->configuration['youtube_video_id'];
    // Construct YouTube embed URL.
    $embed_url = 'https://www.youtube.com/embed/' . $video_id . '?enablejsapi=1&amp;rel=0&amp;controls=0&amp;showinfo=0';
    // Render the HTML for the block.
    return [
      '#markup' => '
        <section class="videowrapper ytvideo">
            <a href="javascript:void(0);" class="close-button"></a>
            <div class="gradient-overlay"></div>
            <i class="fa fa-arrows-alt" aria-hidden="true"></i>
            <iframe width="560" height="315" src="https://www.youtube.com/embed/49psk1jLpYE?si=NOCcY6tuqgBwFFjK" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
        </section>
      ',
    ];
  }

}
