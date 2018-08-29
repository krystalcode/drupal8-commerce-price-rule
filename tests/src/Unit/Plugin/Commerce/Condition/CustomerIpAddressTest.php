<?php

namespace Drupal\Tests\commerce_price_rule\Unit\Plugin\Commerce\Condition;

use Drupal\commerce_price_rule\Plugin\Commerce\Condition\CustomerIpAddress;

use Drupal\Tests\UnitTestCase;
use Drupal\user\UserInterface;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class CustomerIpAddressTest.
 *
 * Tests the customer ip address condition for commerce price rules.
 *
 * @coversDefaultClass \Drupal\commerce_price_rule\Plugin\Commerce\Condition\CustomerIpAddress
 * @group commerce_price_rule
 * @package Drupal\Tests\commerce_price_rule\Unit
 */
class CustomerIpAddressTest extends UnitTestCase {

  /**
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Mock a request.
    $request = $this->prophesize(Request::class);
    $request->getClientIp()->willReturn('127.0.0.1');
    $request = $request->reveal();

    // Mock the request_stack service, make it return our mocked request,
    // and register it in the container.
    $this->requestStack = $this->prophesize(RequestStack::class);
    $this->requestStack->getCurrentRequest()->willReturn($request);
    $this->requestStack = $this->requestStack->reveal();

    $container = new ContainerBuilder();
    $container->set('request_stack', $this->requestStack);
    \Drupal::setContainer($container);
  }

  /**
   * @covers ::evaluate
   */
  public function testEvaluate() {
    $configuration = [];
    $configuration['type'] = 0;
    $configuration['whitelist'] = ['192.168.1.2'];

    $condition = new CustomerIpAddress(
      $configuration,
      'price_rule_customer_ip_address',
      ['entity_type' => 'user'],
      $this->requestStack
    );

    /** @var \Drupal\user\UserInterface $user */
    $user = $this->prophesize(UserInterface::class);
    $user->getEntityTypeId()->willReturn('user');
    $user = $user->reveal();

    $this->assertFalse($condition->evaluate($user));
    $configuration['whitelist'][] = '127.0.0.1';
    $condition->setConfiguration($configuration);
    $this->assertTrue($condition->evaluate($user));
    $configuration['type'] = 1;
    $configuration['blacklist'] = ['192.168.1.2'];
    $condition->setConfiguration($configuration);
    $this->assertTrue($condition->evaluate($user));
    $configuration['blacklist'][] = '127.0.0.1';
    $condition->setConfiguration($configuration);
    $this->assertFalse($condition->evaluate($user));
  }

}
