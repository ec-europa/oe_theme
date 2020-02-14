<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_event\Plugin\ExtraField\Display;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\extra_field\Plugin\ExtraFieldDisplayFormattedBase;

/**
 * Extra field displaying organiser information on events.
 *
 * @ExtraFieldDisplay(
 *   id = "oe_theme_content_event_organiser",
 *   label = @Translation("Organiser"),
 *   bundles = {
 *     "node.oe_event",
 *   },
 *   visible = true
 * )
 */
class OrganiserExtraField extends ExtraFieldDisplayFormattedBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Organiser');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(ContentEntityInterface $entity) {
    $is_internal = (bool) $entity->get('oe_event_organiser_is_internal')->value;

    // If the organiser is internal and not empty, show it.
    if ($is_internal && !$entity->get('oe_event_organiser_internal')->isEmpty()) {
      $build = [
        '#markup' => $entity->get('oe_event_organiser_internal')->entity->label(),
      ];
      CacheableMetadata::createFromObject($entity->get('oe_event_organiser_internal')->entity)
        ->applyTo($build);
      return $build;
    }

    // If the organiser is not internal and not empty, show it.
    if (!$is_internal && !$entity->get('oe_event_organiser_name')->isEmpty()) {
      return [
        '#markup' => $entity->get('oe_event_organiser_name')->value,
      ];
    }

    // Return an empty array otherwise, so the field can be considered empty.
    return [];
  }

}