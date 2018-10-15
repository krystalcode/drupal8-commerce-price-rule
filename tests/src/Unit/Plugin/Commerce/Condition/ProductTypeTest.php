<?php

namespace Drupal\Tests\commerce_price_rule\Unit\Plugin\Commerce\Condition;

use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\commerce_price_rule\Plugin\Commerce\Condition\ProductType;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Class ProductTypeTest.
 *
 * Tests the product type condition for commerce price rules.
 *
 * @coversDefaultClass \Drupal\commerce_price_rule\Plugin\Commerce\Condition\ProductType
 * @group commerce_price_rule
 * @package Drupal\Tests\commerce_price_rule\Unit
 */
class ProductTypeTest extends UnitTestCase {

  /**
   * @covers ::evaluate
   */
  public function testEvaluate() {
    $entity_type_manager = $this->prophesize(EntityTypeManagerInterface::class);
    $entity_type_manager = $entity_type_manager->reveal();

    $configuration = [];
    $configuration['product_types'] = ['hats'];
    $condition = new ProductType(
      $configuration,
      'price_rule_product_type',
      ['entity_type' => 'commerce_product'],
      $entity_type_manager
    );

    /** @var \Drupal\commerce_product\Entity\ProductInterface $product */
    $product = $this->prophesize(ProductInterface::class);
    $product->getEntityTypeId()->willReturn('commerce_product');
    $product->bundle()->willReturn('tops');
    $product = $product->reveal();

    $this->assertFalse($condition->evaluate($product));
    $configuration['product_types'] = ['hats', 'tops'];
    $condition->setConfiguration($configuration);
    $this->assertTrue($condition->evaluate($product));
  }

}
