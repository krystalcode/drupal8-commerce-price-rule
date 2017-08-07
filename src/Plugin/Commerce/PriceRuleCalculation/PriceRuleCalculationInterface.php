<?php

namespace Drupal\commerce_price_rule\Plugin\Commerce\PriceRuleCalculation;

use Drupal\commerce_price_rule\Entity\PriceRuleInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Defines the interface for price rule calculations.
 */
interface PriceRuleCalculationInterface extends ConfigurablePluginInterface, PluginFormInterface, PluginInspectionInterface {

  /**
   * Gets the calculation entity type ID.
   *
   * This is the entity type ID of the entity passed to apply().
   *
   * @return string
   *   The calculation's entity type ID.
   */
  public function getEntityTypeId();

  /**
   * Applies the calculation to the given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param \Drupal\commerce_price_rule\Entity\PriceRuleInterface $price_rule
   *   THe parent price rule.
   */
  public function calculate(EntityInterface $entity, PriceRuleInterface $price_rule);

}
