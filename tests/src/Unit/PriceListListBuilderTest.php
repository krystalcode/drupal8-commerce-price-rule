<?php

namespace Drupal\Tests\commerce_price_rule\Unit;

use Drupal\commerce_price_rule\PriceListListBuilder;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Tests\UnitTestCase;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class PriceListListBuilderTest.
 *
 * Tests the PriceListListBuilderTest form functions.
 *
 * @coversDefaultClass \Drupal\commerce_price_rule\PriceListListBuilder
 * @group commerce_price_rule
 * @package Drupal\Tests\commerce_price_rule\Unit
 */
class PriceListListBuilderTest extends UnitTestCase {

  use StringTranslationTrait;

  /**
   * The dependency injection container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerBuilder
   */
  protected $container;

  /**
   * The entity type definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $entityType;

  /**
   * The entity storage definition.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The PriceListListBuilder class.
   *
   * @var \Drupal\commerce_price_rule\PriceListListBuilder
   */
  protected $priceListListBuilder;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->entityType = $this->createMock(EntityTypeInterface::class);
    $this->storage = $this->createMock(EntityStorageInterface::class);
    $this->formBuilder = $this->createMock(FormBuilderInterface::class);

    $this->container = new ContainerBuilder();
    $this->container->set('string_translation', $this->getStringTranslationStub());
    \Drupal::setContainer($this->container);

    $this->priceListListBuilder = new PriceListListBuilder(
      $this->entityType,
      $this->storage,
      $this->formBuilder
    );
  }

  /**
   * @covers ::getFormId
   */
  public function testGetFormId() {
    $this->assertEquals('commerce_price_rule_lists', $this->priceListListBuilder->getFormId());
  }

  /**
   * @covers ::BuildHeader
   */
  public function testBuildHeader() {
    $expected_header = [];
    $expected_header['name'] = $this->t('Name');
    $expected_header['description'] = $this->t('Description');
    $expected_header['operations'] = $this->t('Operations');

    $header = $this->priceListListBuilder->buildHeader();

    $this->assertEquals($expected_header, $header);
  }

}
