<?php

namespace Drupal\commerce_price_rule\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines the price rule entity class.
 *
 * @ContentEntityType(
 *   id = "commerce_price_group_item",
 *   label = @Translation("Price group item"),
 *   label_collection = @Translation("Price group items"),
 *   label_singular = @Translation("Price group item"),
 *   label_plural = @Translation("Price group items"),
 *   label_count = @PluralTranslation(
 *     singular = "@count price group item",
 *     plural = "@count price group items",
 *   ),
 *   handlers = {
 *     "access" = "Drupal\commerce\EntityAccessControlHandler",
 *     "permission_provider" = "Drupal\commerce\EntityPermissionProvider",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\commerce_price_rule\PriceGroupItemListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\commerce_price_rule\Form\PriceGroupItemForm",
 *       "add" = "Drupal\commerce_price_rule\Form\PriceGroupItemForm",
 *       "edit" = "Drupal\commerce_price_rule\Form\PriceGroupItemForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler"
 *   },
 *   base_table = "commerce_price_group_item",
 *   admin_permission = "administer commerce_price_group_item",
 *   entity_keys = {
 *     "id" = "price_group_item_id",
 *     "label" = "name",
 *     "langcode" = "langcode",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *   },
 *   links = {
 *     "add-form" = "/price-group-item/add",
 *     "edit-form" = "/price-group-item/{commerce_price_group_item}/edit",
 *     "delete-form" = "/price-group-item/{commerce_price_group_item}/delete",
 *     "collection" = "/admin/commerce/price-group-items",
 *   },
 * )
 */
class PriceGroupItem extends ContentEntityBase implements PriceGroupItemInterface {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    if (!$this->get('type')->isEmpty()) {
      return $this->get('type')->first()->getTargetInstance();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setType($type) {
    $this->type->target_plugin_id = $type->getPluginId();
    $this->type->target_plugin_configuration = $type->getConfiguration();
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
  public function getWeight() {
    return (int) $this->get('weight')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->set('weight', $weight);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The price rule name.'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['type'] = BaseFieldDefinition::create('commerce_plugin_item:commerce_price_group_type')
      ->setLabel(t('Type'))
      ->setCardinality(1)
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'commerce_plugin_radios',
        'weight' => 3,
      ]);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDescription(t('Whether the price rule is enabled.'))
      ->setDefaultValue(TRUE)
      ->setRequired(TRUE)
      ->setSettings([
        'on_label' => t('Enabled'),
        'off_label' => t('Disabled'),
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_buttons',
        'weight' => 0,
      ]);

    $fields['weight'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Weight'))
      ->setDescription(t('The weight of this price rule in relation to others.'))
      ->setDefaultValue(0);

    return $fields;
  }

  /**
   * Helper callback for uasort() to sort price rules by weight and label.
   *
   * @param \Drupal\commerce_price_rule\Entity\PriceGroupItemInterface $a
   *   The first price rule to sort.
   * @param \Drupal\commerce_price_rule\Entity\PriceGroupItemInterface $b
   *   The second price rule to sort.
   *
   * @return int
   *   The comparison result for uasort().
   */
  public static function sort(PriceGroupItemInterface $a, PriceGroupItemInterface $b) {
    $a_weight = $a->getWeight();
    $b_weight = $b->getWeight();
    if ($a_weight == $b_weight) {
      $a_label = $a->label();
      $b_label = $b->label();
      return strnatcasecmp($a_label, $b_label);
    }
    return ($a_weight < $b_weight) ? -1 : 1;
  }

}
