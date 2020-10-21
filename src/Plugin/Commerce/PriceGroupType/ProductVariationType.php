<?php

namespace Drupal\commerce_price_rule\Plugin\Commerce\PriceGroupType;

use Drupal\commerce_price\RounderInterface;
use Drupal\commerce\EntityHelper;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Price group type that creates a group of different priduct variation types.
 *
 * Provides a config form for selecting the desired
 * variation types for the group.
 *
 * @CommercePriceGroupType(
 *   id = "product_variation_type",
 *   label = @Translation("Product Variation Type"),
 *   entity_type = "commerce_product_variation",
 * )
 */
class ProductVariationType extends PriceGroupCalculationBase {

  /**
   * The product type storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $productVariationTypeStorage;

  /**
   * Constructs a new PriceGroupType object.
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
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    RounderInterface $rounder,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $rounder
    );
    $this->entityTypeManager = $entity_type_manager;
    $this->EntityFieldManager = $entity_field_manager;
    $this->productVariationTypeStorage = $entity_type_manager->getStorage('commerce_product_variation_type');
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
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'product_type' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $product_types = EntityHelper::extractLabels(
      $this->productVariationTypeStorage->loadMultiple()
    );
    $form['product_variation_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Product variation types'),
      '#options' => $product_types,
      '#default_value' => $this->configuration['product_types'],
      '#tags' => TRUE,
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $values = $form_state->getValue($form['#parents']);
    $this->configuration['product_variation_types'] = $values;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->t('Product type');
  }

}
