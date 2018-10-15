<?php

namespace Drupal\Tests\commerce_price_rule\Unit\Plugin\Commerce\Condition;

use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\commerce_price_rule\Plugin\Commerce\Condition\ProductVariationType;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Class ProductVariationTypeTest.
 *
 * Tests the product variation type condition for commerce price rules.
 *
 * @coversDefaultClass \Drupal\commerce_price_rule\Plugin\Commerce\Condition\ProductVariationType
 * @group commerce_price_rule
 * @package Drupal\Tests\commerce_price_rule\Unit
 */
class ProductVariationTypeTest extends UnitTestCase {

  /**
   * @covers ::evaluate
   */
  public function testEvaluate() {
    $entity_type_manager = $this->prophesize(EntityTypeManagerInterface::class);
    $entity_type_manager = $entity_type_manager->reveal();

    $configuration = [];
    $configuration['product_variation_types'] = ['default'];
    $condition = new ProductVariationType(
      $configuration,
      'price_rule_product_variation_type',
      ['entity_type' => 'commerce_product_variation'],
      $entity_type_manager
    );

    /** @var \Drupal\commerce_product\Entity\ProductVariationInterface $product_variation */
    $product_variation = $this->prophesize(ProductVariationInterface::class);
    $product_variation->getEntityTypeId()
      ->willReturn('commerce_product_variation');
    $product_variation->bundle()->willReturn('standard');
    $product_variation = $product_variation->reveal();

    $this->assertFalse($condition->evaluate($product_variation));
    $configuration['product_variation_types'] = ['default', 'standard'];
    $condition->setConfiguration($configuration);
    $this->assertTrue($condition->evaluate($product_variation));
  }

}
