<?php

namespace Drupal\commerce_price_rule\Entity;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Defines the interface for price rules.
 */
interface PriceGroupItemInterface extends ContentEntityInterface {

  /**
   * Gets the price rule name.
   *
   * @return string
   *   The price rule name.
   */
  public function getName();

  /**
   * Sets the price rule name.
   *
   * @param string $name
   *   The price rule name.
   *
   * @return $this
   */
  public function setName($name);

  /**
   * Gets the calculation.
   *
   * @return \Drupal\commerce_price_rule\Plugin\Commerce\PriceGroupType\PriceGroupTypeInterface|null
   *   The calculation, or NULL if not yet available.
   */
  public function getType();

  /**
   * Sets the type plugin.
   *
   * @param \Drupal\commerce_price_rule\Plugin\Commerce\PriceGroupType\PriceGroupTypeInterface $type
   *   The type plugin.
   *
   * @return $this
   */
  public function setType($type);

  /**
   * Get whether the price rule is enabled.
   *
   * @return bool
   *   TRUE if the price rule is enabled, FALSE otherwise.
   */
  public function isEnabled();

  /**
   * Sets whether the price rule is enabled.
   *
   * @param bool $enabled
   *   Whether the price rule is enabled.
   *
   * @return $this
   */
  public function setEnabled($enabled);

  /**
   * Gets the weight.
   *
   * @return int
   *   The weight.
   */
  public function getWeight();

  /**
   * Sets the weight.
   *
   * @param int $weight
   *   The weight.
   *
   * @return $this
   */
  public function setWeight($weight);

}
