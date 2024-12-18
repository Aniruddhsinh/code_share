<?php

namespace Drupal\example\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'sku_count_link' formatter.
 *
 * @FieldFormatter(
 *   id = "sku_count_link",
 *   label = @Translation("SKU Count Link"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class SKUCountLinkFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Displays the link for SKU Count redirect to SKU listing page.');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    $entity = $items->getEntity();
    foreach ($items as $delta => $item) {
      // Render each element as markup.
      $node_link = "/advertiser/skus?skus=" . $entity->getTitle();
      $link = "<a target='_blank' title='Opens in a new widnow' href='$node_link'>$item->value</a>";
      $element[$delta] = ['#markup' => $link];
    }

    return $element;
  }

}
