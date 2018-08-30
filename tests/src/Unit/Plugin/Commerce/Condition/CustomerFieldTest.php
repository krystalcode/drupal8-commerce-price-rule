<?php

namespace Drupal\Tests\commerce_price_rule\Unit\Plugin\Commerce\Condition;

use Drupal\commerce_price_rule\Plugin\Commerce\Condition\CustomerField;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\Tests\UnitTestCase;
use Drupal\user\UserInterface;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class CustomerFieldTest.
 *
 * Tests the customer field condition for commerce price rules.
 *
 * @coversDefaultClass \Drupal\commerce_price_rule\Plugin\Commerce\Condition\CustomerField
 * @group commerce_price_rule
 * @package Drupal\Tests\commerce_price_rule\Unit
 */
class CustomerFieldTest extends UnitTestCase {

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->entityFieldManager = $this->prophesize(EntityFieldManagerInterface::class);
    $this->entityFieldManager = $this->entityFieldManager->reveal();

    $container = new ContainerBuilder();
    $container->set('entity_field.manager', $this->entityFieldManager);
    \Drupal::setContainer($container);
  }

  /**
   * @covers ::evaluate
   */
  public function testEvaluate() {
    $configuration = [
      'field_name' => 'field_my_custom_field',
      'field_value' => 2,
    ];
    $condition = new CustomerField(
      $configuration,
      'price_rule_customer_field',
      ['entity_type' => 'user'],
      $this->entityFieldManager
    );

    /** @var \Drupal\Core\Field\FieldItemList $field */
    $field = $this->prophesize(FieldItemList::class);
    $field->getName()->willReturn('field_my_custom_field');
    $field->getValue()->willReturn([0 => ['value' => 5]]);
    $field = $field->reveal();

    /** @var \Drupal\user\UserInterface $user */
    $user = $this->prophesize(UserInterface::class);
    $user->getEntityTypeId()->willReturn('user');
    $user->get('field_my_custom_field')->willReturn($field);
    $user = $user->reveal();

    $this->assertFalse($condition->evaluate($user));
    $configuration['field_value'] = 5;
    $condition->setConfiguration($configuration);
    $this->assertTrue($condition->evaluate($user));
  }

}
