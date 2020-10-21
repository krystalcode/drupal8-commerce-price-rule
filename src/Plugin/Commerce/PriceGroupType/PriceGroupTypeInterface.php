<?php

namespace Drupal\commerce_price_rule\Plugin\Commerce\PriceGroupType;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Defines the interface for price rule calculations.
 */
interface PriceGroupTypeInterface extends ConfigurablePluginInterface, PluginFormInterface, PluginInspectionInterface {

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
   * Gets an explanatory label for the calculation.
   *
   * For example, a calculation that calculates the price as a percentage off
   * the base product price could return a label "20% off the product price".
   *
   * @return string
   *   The label.
   */
  public function getLabel();

}
