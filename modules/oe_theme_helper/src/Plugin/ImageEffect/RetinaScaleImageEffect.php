<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_helper\Plugin\ImageEffect;

use Drupal\Component\Utility\Image;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\Plugin\ImageEffect\ScaleImageEffect;

/**
 * Scales an image and upscales it for retina screens if needed.
 *
 * @ImageEffect(
 *   id = "retina_image_scale",
 *   label = @Translation("Retina Image Scale"),
 *   description = @Translation("Scaling will maintain the aspect-ratio of the original image. If only a single dimension is specified, the other dimension will be calculated. If the image is smaller than the specified dimensions, it will be upscaled according to the multiplier value.")
 * )
 */
class RetinaScaleImageEffect extends ScaleImageEffect {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    $target_with = $this->configuration['width'];
    $target_height = $this->configuration['height'];
    // If we are not upscaling the image, check to see if it's smaller
    // than the defined dimensions.
    if (!$upscale = $this->configuration['upscale']) {
      if (
      (!empty($target_with) && $target_with > $image->getWidth()) ||
      (!empty($target_height) && $target_height > $image->getHeight())
      ) {
        // If the image is smaller than the defined dimensions,
        // upscale it according to the defined multiplier.
        $target_with = $image->getWidth() * $this->configuration['multiplier'];
        $target_height = $image->getHeight() * $this->configuration['multiplier'];
        $upscale = TRUE;
      }
    }
    if (!$image->scale($target_with, $target_height, $upscale)) {
      $this->logger->error('Image scale failed using the %toolkit toolkit on %path (%mimetype, %dimensions)', [
        '%toolkit' => $image->getToolkitId(),
        '%path' => $image->getSource(),
        '%mimetype' => $image->getMimeType(),
        '%dimensions' => $image->getWidth() . 'x' . $image->getHeight(),
      ]);
      return FALSE;
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function transformDimensions(array &$dimensions, $uri) {
    if ($dimensions['width'] && $dimensions['height']) {
      $target_with = $this->configuration['width'];
      $target_height = $this->configuration['height'];
      // If we are not upscaling the image, check to see if it's smaller
      // than the defined dimensions.
      if (!$upscale = $this->configuration['upscale']) {
        if (
          (!empty($target_with) && $this->configuration['width'] > $dimensions['width']) ||
          (!empty($target_height) && $this->configuration['height'] > $dimensions['height'])
        ) {
          // If the image is smaller than the defined dimensions,
          // upscale it according to the defined multiplier.
          $target_with = $dimensions['width'] * $this->configuration['multiplier'];
          $target_height = $dimensions['height'] * $this->configuration['multiplier'];
          $upscale = TRUE;
        }
      }
      Image::scaleDimensions($dimensions, $target_with, $target_height, $upscale);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    // Since we are extending the image scale effect and not altering it in any
    // major way, we use the same theme.
    $summary = [
      '#theme' => 'image_scale_summary',
      '#data' => $this->configuration,
    ];
    $summary += parent::getSummary();

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'multiplier' => 2,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['multiplier'] = [
      '#type' => 'number',
      '#title' => t('Multiplier'),
      '#default_value' => $this->configuration['multiplier'],
      '#required' => TRUE,
      '#description' => t("The image will be upscaled according to this multiplier if it is smaller than the dimensions defined in the 'Width' and 'Height' properties."),
      '#min' => 1,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['multiplier'] = $form_state->getValue('multiplier');
  }

}
