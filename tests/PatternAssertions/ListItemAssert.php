<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme\PatternAssertions;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the list item pattern.
 *
 * @see ./templates/patterns/list_item/list_item.ui_patterns.yml
 */
class ListItemAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions($variant): array {
    $base_selector = 'div' . $this->getBaseItemClass($variant);
    return [
      'title' => [
        [$this, 'assertElementText'],
        $base_selector . '__title.ecl-u-type-prolonged-m.ecl-u-type-bold.ecl-u-mt-xs',
      ],
      'url' => [
        [$this, 'assertElementAttribute'],
        $base_selector . '__title.ecl-u-type-prolonged-m.ecl-u-type-bold.ecl-u-mt-xs a.ecl-link.ecl-link--standalone',
        'href',
      ],
      'meta' => [
        [$this, 'assertElementText'],
        $base_selector . '__meta.ecl-u-type-s.ecl-u-type-color-grey-75',
      ],
      'date' => [
        [$this, 'assertDate'],
        $variant,
      ],
      'description' => [
        [$this, 'assertDescription'],
        $base_selector . '__description.ecl-u-type-paragraph.ecl-u-type-color-grey-100.ecl-u-mt-xs',
      ],
      'image' => [
        [$this, 'assertImage'],
        $variant,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $crawler = new Crawler($html);
    $base_selector = 'article' . $this->getBaseItemClass($variant);
    $list_item = $crawler->filter($base_selector);
    self::assertCount(1, $list_item);
  }

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   */
  protected function getPatternVariant(string $html): string {
    $crawler = new Crawler($html);
    // Check whether it is a date pattern and if so, which one.
    $time_element = $crawler->filter('time');
    if ($time_element->count()) {
      switch ($time_element->attr('class')) {
        case 'ecl-date-block ecl-date-block--date':
          return 'date';

        case 'ecl-date-block ecl-date-block--ongoing':
          return 'date_ongoing';

        case 'ecl-date-block ecl-date-block--canceled':
          return 'date_cancelled';

        case 'ecl-date-block ecl-date-block--past':
          return 'date_past';
      }
    }
    // Check whether it is a card and if so,
    // check if it is a highlight or a block.
    $card_element = $crawler->filter('article.ecl-card');
    if ($card_element->count()) {
      // Try to find an image.
      $image = $card_element->filter('div.ecl-card__image');
      if ($image->count()) {
        return 'highlight';
      }
      return 'block';
    }

    // Check whether it is a primary or secondaty thumbnail.
    $primary_thumbnail = $crawler->filter('div.ecl-content-item__image__before');
    if ($primary_thumbnail->count()) {
      return 'thumbnail_primary';
    }
    $primary_secondary = $crawler->filter('div.ecl-content-item__image__after');
    if ($primary_secondary->count()) {
      return 'thumbnail_secondary';
    }
    // At this point, its either a navigation or a default pattern. Currently
    // there is no possible way to know because the only difference is whether
    // metadata is present or not and the metadata is an optional field.
    // Assume default for now.
    return 'default';
  }

  /**
   * Asserts the date block of a list item.
   *
   * @param array|null $expected_date
   *   The expected date values.
   * @param string $variant
   *   The variant of the pattern being checked.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertDate($expected_date, string $variant, Crawler $crawler): void {
    $variant_class = 'ecl-date-block--date';
    switch ($variant) {
      case 'date_ongoing':
        $variant_class = 'ecl-date-block--ongoing';
        break;

      case 'date_past':
        $variant_class = 'ecl-date-block--past';
        break;

      case 'date_cancelled':
        $variant_class = 'ecl-date-block--canceled';
        break;
    }
    $date_block_selector = 'div.ecl-content-item-date__date.ecl-u-flex-grow-0.ecl-u-mr-m time.' . $variant_class;
    if (!$expected_date) {
      $this->assertElementNotExists($date_block_selector, $crawler);
      return;
    }
    $this->assertElementExists($date_block_selector, $crawler);
    $date_block = $crawler->filter($date_block_selector);
    $expected_datetime = $expected_date['year'] . '-' . $expected_date['month'] . '-' . $expected_date['day'];
    self::assertEquals($expected_datetime, $date_block->attr('datetime'));
    self::assertEquals($expected_date['day'], $date_block->filter('span.ecl-date-block__day')->text());
    self::assertEquals($expected_date['month_name'], $date_block->filter('abbr.ecl-date-block__month')->text());
    self::assertEquals($expected_date['year'], $date_block->filter('span.ecl-date-block__year')->text());
  }

  /**
   * Asserts the image block of a list item.
   *
   * @param array|null $expected_image
   *   The expected image values.
   * @param string $variant
   *   The variant of the pattern being checked.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertImage($expected_image, string $variant, Crawler $crawler): void {
    $variant_class = $variant === 'thumbnail_primary' ? 'ecl-content-item__image__before' : 'ecl-content-item__image__after';
    $image_div_selector = 'div.' . $variant_class;
    if (!$expected_image) {
      $this->assertElementNotExists($image_div_selector, $crawler);
      return;
    }
    $this->assertElementExists($image_div_selector, $crawler);
    $image_div = $crawler->filter($image_div_selector);
    self::assertEquals($expected_image['alt'], $image_div->attr('aria-label'));
    self::assertContains($expected_image['src'], $image_div->style('aria-label'));
  }

  /**
   * Asserts the description of the list item.
   *
   * @param array|null $expected
   *   The expected description values.
   * @param string $variant
   *   The variant of the pattern being checked.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertDescription($expected, string $variant, Crawler $crawler): void {
    $base_selector = $this->getBaseItemClass($variant);
    $description_selector = 'div' . $base_selector . '__description.ecl-u-type-paragraph.ecl-u-type-color-grey-100.ecl-u-mt-xs';
    $this->assertElementExists($description_selector, $crawler);
    $description_element = $crawler->filter($description_selector);
    if ($expected instanceof PatternAssertStateInterface) {
      $expected->assert($description_element->html());
      return;
    }
    self::assertEquals($expected, $description_element->filter('p')->html());
  }

  /**
   * Returns the base CSS selector for a list item depending on the variant.
   *
   * @param string $variant
   *   The variant being checked.
   *
   * @return string
   *   The base selector for the variant.
   */
  protected function getBaseItemClass(string $variant): string {
    if (strpos($variant, 'date') !== FALSE) {
      return '.ecl-content-item-date';
    }
    return '.ecl-content-item';
  }

}
