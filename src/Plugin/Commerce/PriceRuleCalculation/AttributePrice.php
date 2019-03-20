<?php

namespace Drupal\commerce_price_rule\Plugin\Commerce\PriceRuleCalculation;

use Drupal\commerce\Context;
use Drupal\commerce_price\Price;
use Drupal\commerce_price\RounderInterface;
use Drupal\commerce_price_rule\Entity\PriceRuleInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\commerce_product\Entity\ProductAttribute;
use Drupal\commerce_product\ProductAttributeFieldManagerInterface;

/**
 * Calculation that sets the variation price based on attribute prices.
 *
 * Provides a config form for setting the attribute price fields which should
 * be used for calculations. Variation prices are updated by adding
 * the sum of all used attribute prices.
 *
 * @CommercePriceRuleCalculation(
 *   id = "attribute_price",
 *   label = @Translation("Get product prices from attribute"),
 *   entity_type = "commerce_product_variation",
 * )
 */
class AttributePrice extends PriceRuleCalculationBase {

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param Drupal\commerce_product\ProductAttributeFieldManagerInterface $attribute_field_manager
   *   The attribute field manager.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    RounderInterface $rounder,
    EntityTypeManagerInterface $entity_type_manager,
    EntityFieldManagerInterface $entity_field_manager,
    ProductAttributeFieldManagerInterface $attribute_field_manager
  ) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $rounder
    );
    $this->entityTypeManager = $entity_type_manager;
    $this->EntityFieldManager = $entity_field_manager;
    $this->attributeFieldManager = $attribute_field_manager;
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
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('commerce_product.attribute_field_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'attribute_price' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form += parent::buildConfigurationForm($form, $form_state);
    $attributes = $this->getAttributesPriceField();
    $form['price'] = [
      '#type' => 'details',
      '#title' => $this->t('Attribute price field mapping'),
      '#open' => TRUE,
    ];

    // Loop through all attributes and display the price fields.
    foreach ($attributes as $key => $price_fields) {
      $field = NULL;
      if (!empty($this->configuration['attribute_price']['price'][$key]['field'])) {
        $field = $this->configuration['attribute_price']['price'][$key]['field'];
      }
      $form['price'][$key]['field'] = [
        '#type' => 'select',
        '#title' => $key,
        '#options' => $price_fields['commerce_price_fields'],
        '#description' => $this->t('Select the field from which the attribute price needs to be fetched'),
        '#default_value' => $field,
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $values = $form_state->getValue($form['#parents']);
    $this->configuration['attribute_price'] = $values;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Attribute-based price');
  }

  /**
   * {@inheritdoc}
   */
  public function calculate(
    EntityInterface $entity,
    PriceRuleInterface $price_rule,
    $quantity,
    Context $context
  ) {
    $this->assertEntity($entity);
    $base_price = $entity->getPrice();
    $attribute_price = $this->getAttributeTotalPrice($entity);
    $price = $base_price->add($attribute_price);
    return $price;
  }

  /**
   * Retrieves all attributes used by product variation types.
   *
   * @return array
   *   Associative array which has product variation type as key and
   *   the attributes used as value.
   */
  public function getAttributesPriceField() {
    $price_fields = [];
    $attributes = ProductAttribute::loadMultiple();
    foreach ($attributes as $attribute) {
      $fields = $this->EntityFieldManager->getFieldDefinitions('commerce_product_attribute_value', $attribute->id());
      foreach ($fields as $field) {
        $type = $field->getType();
        if ($type == "commerce_price") {
          $price_fields[$attribute->id()]['commerce_price_fields'][$field->get('field_name')] = $field->get('label');
        }
      }
    }
    return $price_fields;
  }

  /**
   * Provides the total price of attribues attached to a variation.
   *
   * @param Drupal\commerce_product\Entity\ProductVariation $variation
   *   The commerce product variation.
   *
   * @return Drupal\commerce_price\Price
   *   Sum of all attribute values associated with a variation.
   */
  public function getAttributeTotalPrice(ProductVariation $variation) {
    $attribute_field_map = $this->configuration['attribute_price']['price'];
    $attribute_total_price = new Price((string) 0, $variation->get('price')->first()->toPrice()->getCurrencyCode());
    $attribute_map = $this->attributeFieldManager->getFieldMap($variation->bundle());
    $attributes = array_column($attribute_map, 'attribute_id');
    $attributes = ProductAttribute::loadMultiple($attributes);
    if (empty($attributes)) {
      return $attribute_total_price;
    }
    foreach ($attributes as $attribute) {
      // The attribute field name of variation type will always be in the
      // format of `attrubute_attribute_machine_name`.
      $attribute_field_name = 'attribute_' . $attribute->id();
      $attribute_value = $variation->get($attribute_field_name)->entity;
      if (!empty($attribute_value) && $attribute_value->hasField($attribute_field_map[$attribute->id()]['field'])) {
        $attribute_price = $attribute_value->get($attribute_field_map[$attribute->id()]['field'])->first()->toPrice();
        $attribute_total_price = $attribute_total_price->add($attribute_price);
      }
    }
    return $attribute_total_price;
  }

}
