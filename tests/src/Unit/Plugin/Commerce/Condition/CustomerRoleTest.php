<?php

namespace Drupal\Tests\commerce_price_rule\Unit\Plugin\Commerce\Condition;

use Drupal\commerce_price_rule\Plugin\Commerce\Condition\CustomerRole;

use Drupal\Tests\UnitTestCase;
use Drupal\user\UserInterface;

/**
 * Class CustomerRoleTest.
 *
 * Tests the customer role condition for commerce price rules.
 *
 * @coversDefaultClass \Drupal\commerce_price_rule\Plugin\Commerce\Condition\CustomerRole
 * @group commerce_price_rule
 * @package Drupal\Tests\commerce_price_rule\Unit
 */
class CustomerRoleTest extends UnitTestCase {

  /**
   * @covers ::evaluate
   */
  public function testAnonymousCustomer() {
    $configuration = [];
    $configuration['roles'] = ['authenticated'];
    $condition = new CustomerRole(
      $configuration,
      'price_rule_customer_role',
      ['entity_type' => 'user']
    );

    /** @var \Drupal\user\UserInterface $user */
    $user = $this->prophesize(UserInterface::class);
    $user->getEntityTypeId()->willReturn('user');
    $user->getRoles()->willReturn(['anonymous']);
    $user = $user->reveal();

    $this->assertFalse($condition->evaluate($user));
    $condition->setConfiguration(['roles' => ['anonymous', 'authenticated']]);
    $this->assertTrue($condition->evaluate($user));
  }

  /**
   * @covers ::evaluate
   */
  public function testAuthenticatedCustomer() {
    $condition = new CustomerRole([
      'roles' => ['anonymous'],
    ], 'price_rule_customer_role', ['entity_type' => 'user']);

    /** @var \Drupal\user\UserInterface $user */
    $user = $this->prophesize(UserInterface::class);
    $user->getEntityTypeId()->willReturn('user');
    $user->getRoles()->willReturn(['authenticated']);
    $user = $user->reveal();

    $this->assertFalse($condition->evaluate($user));
    $configuration['roles'] = ['anonymous', 'authenticated'];
    $condition->setConfiguration($configuration);
    $this->assertTrue($condition->evaluate($user));
  }

}
