<?php

namespace Drupal\Tests\commerce_price_rule\Unit\Plugin\Commerce\PriceRuleCalculation;

use Drupal\commerce\Context;
use Drupal\commerce_price\Entity\CurrencyInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_price\Rounder;
use Drupal\commerce_price_rule\Entity\PriceListInterface;
use Drupal\commerce_price_rule\Entity\PriceRuleInterface;
use Drupal\commerce_price_rule\Plugin\Commerce\PriceRuleCalculation\PriceList;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\commerce_store\Entity\StoreInterface;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Driver\sqlite\Statement;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\user\UserInterface;

/**
 * Class PriceListTest.
 *
 * Tests the price list price rule calculation for commerce price rules.
 *
 * @coversDefaultClass \Drupal\commerce_price_rule\Plugin\Commerce\PriceRuleCalculation\PriceList
 * @group commerce_price_rule
 * @package Drupal\Tests\commerce_price_rule\Unit
 */
class PriceListTests extends UnitTestCase {

  /**
   * Provides data for testCalculate().
   *
   * @return array
   *   The providers.
   */
  public function calculateProviderConfiguration() {
    // Create a mock database connection.
    $statement = $this->prophesize(Statement::class);
    $statement->fetchAssoc()->willReturn($this->fetchAssocResults());
    $statement = $statement->reveal();

    $query = $this->prophesize(SelectInterface::class);
    $query->fields('li', ['price__number', 'price__currency_code'])->willReturn($this->returnSelf());
    /*$query->condition('li.price_list_id', 1)
      ->willReturn($this->returnSelf());
    $query->condition('li.product_variation_id', 1)
      ->willReturn($this->returnSelf());
    $query->condition('li.status', 1)
      ->willReturn($this->returnSelf());
    $quantity_max_defined = $query->andConditionGroup()
      ->condition('min_quantity', 1, '<=')
      ->condition('max_quantity', 1, '>=')->willReturn($this->returnSelf());
    $quantity_max_undefined = $query->andConditionGroup()
      ->condition('min_quantity', 1, '<=')
      ->condition('max_quantity', NULL, 'IS NULL')->willReturn($this->returnSelf());
    $quantity_query = $query->orConditionGroup()
      ->condition($quantity_max_defined)
      ->condition($quantity_max_undefined)->willReturn($this->returnSelf());
    $query->condition($quantity_query)->willReturn($this->returnSelf());*/
    $query->execute()->willReturn($statement);
    $query = $query->reveal();

    $database = $this->prophesize(Connection::class);
    $database->select('commerce_price_rule_list_item', 'li')->willReturn($query);
    $database = $database->reveal();

    // Test with no price list ID.
    $rounder = $this->getRounder();
    $entity_manager = $this->getEntityManager();
    $configuration = [];
    $price_list = new PriceList(
      $configuration,
      'price_list',
      ['entity_type' => 'commerce_product_variation'],
      $rounder,
      $entity_manager,
      $database
    );
    // Should return right away as we have no price list ID.
    $expected_result = NULL;

    // Test with price list and no results.
    // Create a mock database connection.
    $statement2 = $this->prophesize(Statement::class);
    $statement2->fetchAssoc()->willReturn([]);
    $statement2 = $statement2->reveal();
    $query2 = $this->prophesize(SelectInterface::class);
    $query2->fields('li', ['price__number', 'price__currency_code'])->willReturn($this->returnSelf());
    /*$query2->condition('li.price_list_id', 1)
      ->willReturn($this->returnSelf());
    $query2->condition('li.product_variation_id', 1)
      ->willReturn($this->returnSelf());
    $query2->condition('li.status', 1)
      ->willReturn($this->returnSelf());
    $quantity_max_defined = $query2->andConditionGroup()
      ->condition('min_quantity', 1, '<=')
      ->condition('max_quantity', 1, '>=')->willReturn($this->returnSelf());
    $quantity_max_undefined = $query2->andConditionGroup()
      ->condition('min_quantity', 1, '<=')
      ->condition('max_quantity', NULL, 'IS NULL')->willReturn($this->returnSelf());
    $quantity_query = $query2->orConditionGroup()
      ->condition($quantity_max_defined)
      ->condition($quantity_max_undefined)->willReturn($this->returnSelf());
    $query2->condition($quantity_query)->willReturn($this->returnSelf());*/
    $query2->execute()->willReturn($statement2);
    $query2 = $query2->reveal();
    $database2 = $this->prophesize(Connection::class);
    $database2->select('commerce_price_rule_list_item', 'li')->willReturn($query2);
    $database2 = $database2->reveal();
    $configuration2 = ['price_list_id' => 1];
    $price_list2 = new PriceList(
      $configuration2,
      'price_list',
      ['entity_type' => 'commerce_product_variation'],
      $rounder,
      $entity_manager,
      $database2
    );
    // Should return NULL as the mock db_query returned no results.
    $expected_result2 = NULL;

    // Test with a price list and results.
    $configuration3 = ['price_list_id' => 1];
    $price_list3 = new PriceList(
      $configuration3,
      'price_list',
      ['entity_type' => 'commerce_product_variation'],
      $rounder,
      $entity_manager,
      $database
    );
    // Should return with a price.
    $expected_result3 = new Price('11.99', 'USD');

    return [
      [
        $price_list,
        $expected_result,
      ],
      [
        $price_list2,
        $expected_result2,
      ],
      [
        $price_list3,
        $expected_result3,
      ],
    ];
  }

  /**
   * Test the calculate() function.
   *
   * @param \Drupal\commerce_price_rule\Plugin\Commerce\PriceRuleCalculation\PriceList $price_list
   *   The price list.
   * @param mixed $expected_result
   *   The expected result.
   *
   * @covers ::calculate
   * @dataProvider calculateProviderConfiguration
   */
  public function testCalculate(PriceList $price_list, $expected_result) {
    // Setup arguments needed for the context.
    $user = $this->createMock(UserInterface::class);
    $store = $this->createMock(StoreInterface::class);
    $context = new Context($user, $store);

    // Create a mock price rule.
    $price_rule = $this->prophesize(PriceRuleInterface::class);
    $price_rule = $price_rule->reveal();

    $calculated_price = $price_list->calculate(
      $this->getProductVariation(),
      $price_rule,
      1,
      $context
    );
    $this->assertEquals($expected_result, $calculated_price);
  }

  /**
   * Returns an array of results for the the fetchAssoc() database function.
   *
   * @return array
   *   an array of price information.
   */
  protected function fetchAssocResults() {
    return [
      'price__number' => '11.99',
      'price__currency_code' => 'USD',
    ];
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
    $product_variation->id()->willReturn(1);
    $product_variation->getPrice()->willReturn(
      new Price('50.00', 'USD')
    );
    $product_variation = $product_variation->reveal();

    return $product_variation;
  }

  /**
   * Creates a mock entity manager interface class.
   *
   * @return \Drupal\Core\Entity\EntityManagerInterface
   *   The entity manager.
   */
  protected function getEntityManager() {
    // Create a mock entity manager interface class.
    /** @var \Drupal\commerce_price_rule\Entity\PriceListInterface $storage */
    $storage = $this->prophesize(PriceListInterface::class);
    $storage->load(1)->willReturn([
      'name' => 'price_rule_1',
      'description' => 'Price Rule 1'
    ]);
    $storage = $storage->reveal();

    /** @var \Drupal\Core\Entity\EntityManagerInterface $entity_manager */
    $entity_manager = $this->prophesize(EntityManagerInterface::class);
    $entity_manager->getStorage('commerce_price_rule_list')->willReturn($storage);
    $entity_manager = $entity_manager->reveal();

    return $entity_manager;
  }

}
