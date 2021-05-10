<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_theme_helper\Kernel\Plugin\Field\FieldFormatter;

use Drupal\entity_test\Entity\EntityTestMul;
use Drupal\Tests\address\Kernel\Formatter\FormatterTestBase;

/**
 * Test AddressInlineFormatter plugin.
 *
 * @group batch2
 */
class AddressInlineFormatterTest extends FormatterTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'oe_theme_helper',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->createField('address', 'oe_theme_helper_address_inline');
  }

  /**
   * Tests formatting of address.
   */
  public function testInlineFormatterAddress() {
    $entity = EntityTestMul::create([]);
    $entity->{$this->fieldName} = [
      'country_code' => 'BE',
      'locality' => 'Brussels <Bruxelles>',
      'postal_code' => '1000',
      'address_line1' => 'Rue de la Loi, 56 <123>',
      'address_line2' => 'or \'Wetstraat\' (Dutch), meaning "Law Street"',
    ];

    $this->renderEntityFields($entity, $this->display);
    $expected = 'Rue de la Loi, 56 &lt;123&gt;, or &#039;Wetstraat&#039; (Dutch), meaning &quot;Law Street&quot;, 1000 Brussels &lt;Bruxelles&gt;, Belgium';
    $this->assertRaw($expected);
  }

}
