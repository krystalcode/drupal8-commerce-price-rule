<?php

namespace Drupal\Tests\commerce_price_rule\Unit\Plugin\Commerce\Condition;

use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\commerce_price_rule\Plugin\Commerce\Condition\Product;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Class ProductTest.
 *
 * Tests the product condition for commerce price rules.
 *
 * @coversDefaultClass \Drupal\commerce_price_rule\Plugin\Commerce\Condition\Product
 * @group commerce_price_rule
 * @package Drupal\Tests\commerce_price_rule\Unit
 */
class ProductTest extends UnitTestCase {

  /**
   * @covers ::evaluate
   */
  public function testEvaluate() {
    $entity_type_manager = $this->prophesize(EntityTypeManagerInterface::class);
    $entity_type_manager = $entity_type_manager->reveal();

    $configuration = [];
    $configuration['products'] = [
      ['product_id' => 1],
    ];
    $condition = new Product(
      $configuration,
      'price_rule_product',
      ['entity_type' => 'commerce_product'],
      $entity_type_manager
    );

    /** @var \Drupal\commerce_product\Entity\ProductInterface $product */
    $product = $this->prophesize(ProductInterface::class);
    $product->getEntityTypeId()->willReturn('commerce_product');
    $product->id()->willReturn(2);
    $product = $product->reveal();

    $this->assertFalse($condition->evaluate($product));
    $configuration['products'][0]['product_id'] = 2;
    $condition->setConfiguration($configuration);
    $this->assertTrue($condition->evaluate($product));
  }

}
