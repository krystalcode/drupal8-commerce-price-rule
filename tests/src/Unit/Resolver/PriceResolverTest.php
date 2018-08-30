<?php

namespace Drupal\Tests\commerce_price_rule\Unit\Resolver;

use Drupal\commerce\Context;
use Drupal\commerce_price\Entity\CurrencyInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_price\Rounder;
use Drupal\commerce_price_rule\Entity\PriceRuleInterface;
use Drupal\commerce_price_rule\Resolver\PriceResolver;
use Drupal\commerce_price_rule\Plugin\Commerce\PriceRuleCalculation\FixedAmountOff;
use Drupal\commerce_price_rule\PriceRuleStorageInterface;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\commerce_store\Entity\StoreInterface;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\user\UserInterface;

/**
 * Class PriceResolverTest.
 *
 * Tests the price resolver for commerce price rules.
 *
 * @coversDefaultClass \Drupal\commerce_price_rule\Resolver\PriceResolver
 * @group commerce_price_rule
 * @package Drupal\Tests\commerce_price_rule\Unit
 */
class PriceResolverTest extends UnitTestCase {

  /**
   * @covers ::resolve
   *
   * Tests the resolver when the price rule does not apply.
   */
  public function testResolverDoesNotApply() {
    // Setup arguments needed for the context.
    $user = $this->createMock(UserInterface::class);
    $store = $this->createMock(StoreInterface::class);
    $context = new Context($user, $store);

    $product_variation = $this->getProductVariation();

    // Setup the needed objects for the price resolver.
    /** @var \Drupal\commerce_price_rule\Entity\PriceRuleInterface $price_rule */
    $price_rule = $this->prophesize(PriceRuleInterface::class);
    $price_rule->available($product_variation, 1, $context, [])
      ->willReturn(TRUE);
    // Make the price rule not apply.
    $price_rule->applies($product_variation, 1, $context, [])
      ->willReturn(FALSE);
    $price_rule->calculate($product_variation, 1, $context)
      ->willReturn(new Price('10.00', 'USD'));
    $price_rule = $price_rule->reveal();

    /** @var \Drupal\commerce_price_rule\PriceRuleStorageInterface $storage */
    $storage = $this->prophesize(PriceRuleStorageInterface::class);
    $storage->loadAvailable($store)->willReturn([$price_rule]);
    $storage = $storage->reveal();
    /** @var \Drupal\Core\Entity\EntityManagerInterface $entity_manager */
    $entity_manager = $this->prophesize(EntityManagerInterface::class);
    $entity_manager->getStorage('commerce_price_rule')->willReturn($storage);
    $entity_manager = $entity_manager->reveal();

    // Now, resolve the price.
    $price_resolver = new PriceResolver($entity_manager);
    $resolved_price = $price_resolver->resolve($product_variation, 1, $context);
    $this->assertNull($resolved_price);
  }

  /**
   * @covers ::resolve
   *
   * Tests the resolver when the price rule is not available.
   */
  public function testResolverNotAvailable() {
    // Setup arguments needed for the context.
    $user = $this->createMock(UserInterface::class);
    $store = $this->createMock(StoreInterface::class);
    $context = new Context($user, $store);

    $product_variation = $this->getProductVariation();

    // Setup the needed objects for the price resolver.
    /** @var \Drupal\commerce_price_rule\Entity\PriceRuleInterface $price_rule */
    $price_rule = $this->prophesize(PriceRuleInterface::class);
    // Make the price rule not available.
    $price_rule->available($product_variation, 1, $context, [])
      ->willReturn(FALSE);
    $price_rule->applies($product_variation, 1, $context, [])->willReturn(TRUE);
    $price_rule->calculate($product_variation, 1, $context)
      ->willReturn(new Price('10.00', 'USD'));
    $price_rule = $price_rule->reveal();

    /** @var \Drupal\commerce_price_rule\PriceRuleStorageInterface $storage */
    $storage = $this->prophesize(PriceRuleStorageInterface::class);
    $storage->loadAvailable($store)->willReturn([$price_rule]);
    $storage = $storage->reveal();
    /** @var \Drupal\Core\Entity\EntityManagerInterface $entity_manager */
    $entity_manager = $this->prophesize(EntityManagerInterface::class);
    $entity_manager->getStorage('commerce_price_rule')->willReturn($storage);
    $entity_manager = $entity_manager->reveal();

    // Now, resolve the price.
    $price_resolver = new PriceResolver($entity_manager);
    $resolved_price = $price_resolver->resolve($product_variation, 1, $context);
    $this->assertNull($resolved_price);
  }

  /**
   * @covers ::resolve
   *
   * Tests the resolver when the price rule applies and is available.
   */
  public function testResolverAppliesAndAvailable() {
    // Setup arguments needed for the context.
    $user = $this->createMock(UserInterface::class);
    $store = $this->createMock(StoreInterface::class);
    $context = new Context($user, $store);

    $product_variation = $this->getProductVariation();

    // Setup the needed objects for the price resolver.
    /** @var \Drupal\commerce_price_rule\Entity\PriceRuleInterface $price_rule */
    $price_rule = $this->prophesize(PriceRuleInterface::class);
    $price_rule->available($product_variation, 1, $context, [])
      ->willReturn(TRUE);
    $price_rule->applies($product_variation, 1, $context, [])->willReturn(TRUE);
    $price_rule->calculate($product_variation, 1, $context)
      ->willReturn(new Price('10.00', 'USD'));
    $price_rule = $price_rule->reveal();

    /** @var \Drupal\commerce_price_rule\PriceRuleStorageInterface $storage */
    $storage = $this->prophesize(PriceRuleStorageInterface::class);
    $storage->loadAvailable($store)->willReturn([$price_rule]);
    $storage = $storage->reveal();
    /** @var \Drupal\Core\Entity\EntityManagerInterface $entity_manager */
    $entity_manager = $this->prophesize(EntityManagerInterface::class);
    $entity_manager->getStorage('commerce_price_rule')->willReturn($storage);
    $entity_manager = $entity_manager->reveal();

    // Now, resolve the price.
    $price_resolver = new PriceResolver($entity_manager);
    $resolved_price = $price_resolver->resolve($product_variation, 1, $context);
    $this->assertNotNull($resolved_price);
    $this->assertEquals(new Price('10.00', 'USD'), $resolved_price);
  }

  /**
   * Creates and returns a commerce price rounder object.
   *
   * @return \Drupal\commerce_price\Rounder
   *   The rounder object.
   */
  protected function getRounder() {
    // Setup the rounder to pass to the fixed amount off class.
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
    $product_variation = $product_variation->reveal();

    return $product_variation;
  }

}
