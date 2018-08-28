<?php

namespace Drupal\Tests\commerce_price_rule\Unit\Plugin\Commerce\Condition;

use Drupal\commerce_price_rule\Plugin\Commerce\Condition\CustomerPrivateTempStore;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\TempStore\PrivateTempStore;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Tests\UnitTestCase;
use Drupal\user\UserInterface;

/**
 * Class CustomerPrivateTempStoreTest.
 *
 * Tests the customer private temp store condition for commerce price rules.
 *
 * @coversDefaultClass \Drupal\commerce_price_rule\Plugin\Commerce\Condition\CustomerPrivateTempStore
 * @group commerce_price_rule
 * @package Drupal\Tests\commerce_price_rule\Unit
 */
class CustomerPrivateTempStoreTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    /** @var \Drupal\Core\TempStore\PrivateTempStore $tempStore */
    $tempStore = $this->prophesize(PrivateTempStore::class);
    $tempStore->get('secret_key')->willReturn('abrakadabra');
    $tempStore = $tempStore->reveal();

    /** @var \Drupal\Core\TempStore\PrivateTempStoreFactory $tempStoreFactory */
    $tempStoreFactory = $this->prophesize(PrivateTempStoreFactory::class);
    $tempStoreFactory->get('my_private_store')->willReturn($tempStore);
    $tempStoreFactory = $tempStoreFactory->reveal();

    $this->container = new ContainerBuilder();
    $this->container->set('user.private_tempstore', $tempStoreFactory);
    \Drupal::setContainer($this->container);
  }

  /**
   * @covers ::evaluate
   */
  public function testEvaluate() {
    $configuration = [
      'private_tempstore' => 'my_private_store',
      'key' => 'secret_key',
      'value' => 'not_the_right_value',
      'in_array' => FALSE,
      'match_null' => FALSE,
    ];
    $condition = new CustomerPrivateTempStore(
      $configuration,
      'price_rule_customer_tempstore',
      ['entity_type' => 'user']
    );

    /** @var \Drupal\user\UserInterface $user */
    $user = $this->prophesize(UserInterface::class);
    $user->getEntityTypeId()->willReturn('user');
    $user->id()->willReturn(1);
    $user = $user->reveal();

    $this->assertFalse($condition->evaluate($user));
    $configuration = [
      'private_tempstore' => 'my_private_store',
      'key' => 'secret_key',
      'value' => 'abrakadabra',
      'in_array' => FALSE,
      'match_null' => FALSE,
    ];
    $condition->setConfiguration($configuration);
    $this->assertTrue($condition->evaluate($user));
  }

}
