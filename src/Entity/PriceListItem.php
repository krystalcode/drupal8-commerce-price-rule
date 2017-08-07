<?php

namespace Drupal\commerce_price_rule\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines the Price list item entity class.
 *
 * @ContentEntityType(
 *   id = "commerce_price_rule_list_item",
 *   label = @Translation("Price List Item"),
 *   label_collection = @Translation("Price List Items"),
 *   label_singular = @Translation("price list item"),
 *   label_plural = @Translation("price list items"),
 *   label_count = @PluralTranslation(
 *     singular = "@count price list item",
 *     plural = "@count price list items",
 *   ),
 *   base_table = "commerce_price_rule_list_item",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *   },
 * )
 */
class PriceListItem extends ContentEntityBase implements PriceListItemInterface {

  /**
   * {@inheritdoc}
   */
  public function getPriceRule() {
    return $this->get('price_rule_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getPriceRuleId() {
    return $this->get('price_rule_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getProductVariation() {
    return $this->get('product_variation_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getProductVariationId() {
    return $this->get('product_variation_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getPrice() {
    return $this->get('price')->first()->toPrice();
  }

  /**
   * {@inheritdoc}
   */
  public function setPrice(Price $price) {
    $this->set('price', $price);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setEnabled($enabled) {
    $this->set('status', (bool) $enabled);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['price_rule_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Price rule'))
      ->setDescription(t('The parent price rule.'))
      ->setSetting('target_type', 'commerce_price_rule')
      ->setRequired(TRUE)
      ->setReadOnly(TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['product_variation_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Product variation'))
      ->setDescription(t('The product variation that the price should be applied to.'))
      ->setSetting('target_type', 'commerce_product_variation')
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => -1,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['price'] = BaseFieldDefinition::create('commerce_price')
      ->setLabel(t('Price'))
      ->setDescription(t('The price list item price'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'commerce_price_default',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'commerce_price_default',
        'weight' => 0,
      ])
      ->setRequired(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Enabled'))
      ->setDescription(t('Whether the price list item is enabled.'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 99,
      ])
      ->setDisplayConfigurable('form', TRUE);

    return $fields;
  }

}
