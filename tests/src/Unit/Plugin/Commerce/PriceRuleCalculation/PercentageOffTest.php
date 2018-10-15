<?php

namespace Drupal\Tests\commerce_price_rule\Unit\Plugin\Commerce\PriceRuleCalculation;

use Drupal\commerce\Context;
use Drupal\commerce_price\Entity\CurrencyInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_price\Rounder;
use Drupal\commerce_price_rule\Entity\PriceRuleInterface;
use Drupal\commerce_price_rule\Plugin\Commerce\PriceRuleCalculation\PercentageOff;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\commerce_store\Entity\StoreInterface;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\user\UserInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Class PercentageOffTest.
 *
 * Tests the percentage off price rule calculation for commerce price rules.
 *
 * @coversDefaultClass \Drupal\commerce_price_rule\Plugin\Commerce\PriceRuleCalculation\PercentageOff
 * @group commerce_price_rule
 * @package Drupal\Tests\commerce_price_rule\Unit
 */
class PercentageOffTest extends UnitTestCase {

  /**
   * @covers ::calculate
   */
  public function testCalculate() {
    /** @var \Drupal\commerce_price_rule\Plugin\Commerce\PriceRuleCalculation\PercentageOff $percentage_off */
    $configuration = [];
    $configuration['amount'] = '0.1';
    $percentage_off = new PercentageOff(
      $configuration,
      'percentage_off',
      ['entity_type' => 'commerce_product_variation'],
      $this->getRounder()
    );

    $product_variation = $this->getProductVariation();

    // Setup the arguments to pass to the percentage off calculate function.
    $user = $this->createMock(UserInterface::class);
    $store = $this->createMock(StoreInterface::class);
    $context = new Context($user, $store);

    /** @var \Drupal\commerce_price_rule\Entity\PriceRuleInterface $price_rule */
    $price_rule = $this->prophesize(PriceRuleInterface::class);
    $price_rule = $price_rule->reveal();

    // Calculate the adjusted priced with a 10% off rule.
    $calculated_price = $percentage_off->calculate(
      $product_variation,
      $price_rule,
      1,
      $context
    );
    // Test the logic.
    $this->assertNotEquals(new Price('40.00', 'USD'), $calculated_price);
    $this->assertEquals(new Price('45.00', 'USD'), $calculated_price);
    $this->assertNotEquals(new Price('45', 'EUR'), $calculated_price);
    $this->assertEquals(new Price('45', 'USD'), $calculated_price);

    // Calculate the adjusted priced with a 20% off rule.
    $configuration['amount'] = '0.2';
    $percentage_off->setConfiguration($configuration);
    $calculated_price = $percentage_off->calculate(
      $product_variation,
      $price_rule,
      1,
      $context
    );
    // Test the logic.
    $this->assertNotEquals(new Price('45.00', 'USD'), $calculated_price);
    $this->assertEquals(new Price('40.00', 'USD'), $calculated_price);
    $this->assertNotEquals(new Price('40', 'EUR'), $calculated_price);
    $this->assertEquals(new Price('40', 'USD'), $calculated_price);
  }

  /**
   * Creates and returns a commerce price rounder object.
   *
   * @return \Drupal\commerce_price\Rounder
   *   The rounder object.
   */
  protected function getRounder() {
    // Setup the rounder to pass to the percentage off class.
    $usd_currency = $this->prophesize(CurrencyInterface::class);
    $usd_currency->id()->willReturn('USD');
    $usd_currency->getFractionDigits()->willReturn('2');
    $usd_currency = $usd_currency->reveal();

    $storage = $this->prophesize(EntityStorageInterface::class);
    $storage->load('USD')->willReturn($usd_currency);
    $storage = $storage->reveal();

    $entity_type_manager = $this->prophesize(EntityTypeManagerInterface::class);
    $entity_type_manager->getStorage('commerce_currency')->willReturn($storage);
    $entity_type_manager = $entity_type_manager->reveal();

    $rounder = new Rounder($entity_type_manager);

    return $rounder;
  }

  /**
   * Creates and returns a mock product variation.
   *
   * @return \Drupal\commerce_product\Entity\ProductVariationInterface
   *   The product variation object.
   */
  protected function getProductVariation() {
    // Setup the product variation.
    /** @var \Drupal\commerce_product\Entity\ProductVariationInterface $product_variation */
    $product_variation = $this->prophesize(ProductVariationInterface::class);
    $product_variation->getEntityTypeId()
      ->willReturn('commerce_product_variation');
    $product_variation->getPrice()->willReturn(
      new Price('50.00', 'USD')
    );
    $product_variation = $product_variation->reveal();

    return $product_variation;
  }

}
