<?php

namespace Drupal\commerce_price_rule\Plugin\Commerce\PriceRuleCalculation;

use Drupal\commerce_price\Price;
use Drupal\commerce_price\RounderInterface;
use Drupal\commerce_price_rule\Entity\PriceRuleInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides calculation based on a price list.
 *
 * @CommercePriceRuleCalculation(
 *   id = "price_list",
 *   label = @Translation("Get product prices from a list"),
 *   entity_type = "commerce_product_variation",
 * )
 */
class PriceList extends PriceRuleCalculationBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database_connection;

  /**
   * Constructs a new PriceList object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The pluginId for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\commerce_price\RounderInterface $rounder
   *   The rounder.
   * @param \Drupal\Core\Database\Connection $database_connection
   *   The database connection.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    RounderInterface $rounder,
    $database_connection
  ) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $rounder
    );

    $this->database_connection = $database_connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('commerce_price.rounder'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function calculate(
    EntityInterface $entity,
    PriceRuleInterface $price_rule
  ) {
    $this->assertEntity($entity);

    // Performance is important here, load only the required fields directly
    // from the database.
    $result = $this->database_connection
      ->select('commerce_price_rule_list_item', 'li')
      ->fields('li', ['price__number', 'price__currency_code'])
      ->condition('li.price_rule_id', $price_rule->id())
      ->condition('li.product_variation_id', $entity->id())
      ->condition('li.status', 1)
      ->range(0, 1)
      ->execute()
      ->fetchAssoc();

    if ($result) {
      return new Price(
        $result['price__number'],
        $result['price__currency_code']
      );
    }
  }

}
