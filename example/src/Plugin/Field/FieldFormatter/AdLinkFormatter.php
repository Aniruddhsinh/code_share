<?php

namespace Drupal\example\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'ad_link' formatter.
 *
 * @FieldFormatter(
 *   id = "ad_link",
 *   label = @Translation("AD Link"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class AdLinkFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Displays the link for Ad edit page.');
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
      $node_link = "/node/" . $entity->id() . "/edit";
      $link = "<a target='_blank' title='Opens in a new widnow' href='$node_link'>$item->value</a>";
      $element[$delta] = ['#markup' => $link];
    }

    return $element;
  }

}
