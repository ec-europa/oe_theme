<?php

/**
 * @file
 * OpenEuropa theme helper post updates.
 */

declare(strict_types = 1);

use Drupal\image\Entity\ImageStyle;

/**
 * Use retina image styles on medium and small image styles.
 */
function oe_theme_helper_post_update_use_retina_image_styles(array &$sandbox): void {
  \Drupal::service('plugin.manager.image.effect')->clearCachedDefinitions();

  $image_styles = [
    'oe_theme_medium_2x_no_crop',
    'oe_theme_small_2x_no_crop',
  ];

  foreach ($image_styles as $image_style_name) {
    $image_style = ImageStyle::load($image_style_name);
    $effects = $image_style->getEffects();
    /** @var \Drupal\image\ImageEffectInterface $effect */
    foreach ($effects as $effect) {
      if ($effect->getPluginId() == 'image_scale') {
        $image_style->deleteImageEffect($effect);
        $configuration = $effect->getConfiguration();
        $new_configuration = [
          'id' => 'retina_image_scale',
          'data' => $configuration['data'],
          'weight' => $configuration['weight'],
        ];
        $new_configuration['data']['multiplier'] = 2;
        $image_style->addImageEffect($new_configuration);
        $image_style->save();
        break;
      }
    }
  }
}
