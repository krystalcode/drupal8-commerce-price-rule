<?php

namespace Drupal\commerce_price_rule\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;

/**
 * Defines the price group item add/edit form.
 */
class PriceGroupItemForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->entity->save();
    drupal_set_message($this->t('Saved the %label price group item.', ['%label' => $this->entity->label()]));
    $form_state->setRedirect('entity.commerce_price_group_item.collection');
  }

}
