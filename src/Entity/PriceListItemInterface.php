<?php

namespace Drupal\commerce_price_rule\Entity;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Defines the interface for a price list item.
 */
interface PriceListItemInterface extends ContentEntityInterface {

  /**
   * Gets the parent price rule.
   *
   * @return PriceRuleInterface
   *   The price rule entity.
   */
  public function getPriceRule();

  /**
   * Gets the parent price rule ID.
   *
   * @return int
   *   The price rule ID.
   */
  public function getPriceRuleId();

  /**
   * Get the price list item's product variation.
   *
   * @return \Drupal\commerce_product\Entity\ProductVariation
   *   The product variation that the list item's price applies to.
   */
  public function getProductVariation();

  /**
   * Get the price list item's product variation ID.
   *
   * @return int
   *   The ID of the product variation that the list item's price applies to.
   */
  public function getProductVariationId();

  /**
   * Gets the price.
   *
   * @return \Drupal\commerce_price\Price
   *   The price.
   */
  public function getPrice();

  /**
   * Sets the price.
   *
   * @param \Drupal\commerce_price\Price $price
   *   The price.
   *
   * @return $this
   */
  public function setPrice(Price $price);

  /**
   * Get whether the price list item is enabled.
   *
   * @return bool
   *   TRUE if the price list item is enabled, FALSE otherwise.
   */
  public function isEnabled();

  /**
   * Sets whether the price list item is enabled.
   *
   * @param bool $enabled
   *   Whether the price list item is enabled.
   *
   * @return $this
   */
  public function setEnabled($enabled);
}
