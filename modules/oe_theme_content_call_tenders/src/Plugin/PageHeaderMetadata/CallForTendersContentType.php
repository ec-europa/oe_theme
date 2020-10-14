<?php

declare(strict_types = 1);

namespace Drupal\oe_theme_content_call_tenders\Plugin\PageHeaderMetadata;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oe_theme_helper\Plugin\PageHeaderMetadata\NodeViewRoutesBase;
use Drupal\oe_content_call_tenders\CallForTendersNodeWrapper;

/**
 * Page header metadata for the OpenEuropa "Call for tenders" content type.
 *
 * @PageHeaderMetadata(
 *   id = "oe_call_tenders_content_type",
 *   label = @Translation("Metadata extractor for the OE Call for tenders content type"),
 *   weight = -1
 * )
 */
class CallForTendersContentType extends NodeViewRoutesBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function applies(): bool {
    $node = $this->getNode();

    return $node && $node->bundle() === 'oe_call_tenders';
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(): array {
    $metadata = parent::getMetadata();

    $node = $this->getNode();
    $node_wrapper = CallForTendersNodeWrapper::getInstance($node);
    $metadata['metas'] = [$this->t('Call for tenders')];
    if ($node_wrapper->hasStatus()) {
      $metadata['metas'][] = $node_wrapper->getStatusLabel();
    }

    if ($node->get('oe_summary')->isEmpty()) {
      return $metadata;
    }

    $summary = $node->get('oe_summary')->first();
    $metadata['introduction'] = [
      // We strip the tags because the component expects only one paragraph of
      // text and the field is using a text format which adds paragraph tags.
      '#type' => 'inline_template',
      '#template' => '{{ summary|render|striptags("<strong><a><em>")|raw }}',
      '#context' => [
        'summary' => [
          '#type' => 'processed_text',
          '#text' => $summary->value,
          '#format' => $summary->format,
          '#langcode' => $summary->getLangcode(),
        ],
      ],
    ];

    return $metadata;
  }

}
